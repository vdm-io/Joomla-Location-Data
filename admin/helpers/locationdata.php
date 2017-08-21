<?php
/*--------------------------------------------------------------------------------------------------------|  www.vdm.io  |------/
    __      __       _     _____                 _                                  _     __  __      _   _               _
    \ \    / /      | |   |  __ \               | |                                | |   |  \/  |    | | | |             | |
     \ \  / /_ _ ___| |_  | |  | | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_  | \  / | ___| |_| |__   ___   __| |
      \ \/ / _` / __| __| | |  | |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __| | |\/| |/ _ \ __| '_ \ / _ \ / _` |
       \  / (_| \__ \ |_  | |__| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | |  | |  __/ |_| | | | (_) | (_| |
        \/ \__,_|___/\__| |_____/ \___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__| |_|  |_|\___|\__|_| |_|\___/ \__,_|
                                                        | |                                                                 
                                                        |_| 				
/-------------------------------------------------------------------------------------------------------------------------------/

	@version		1.0.1
	@build			20th August, 2017
	@created		28th June, 2016
	@package		Location Data
	@subpackage		locationdata.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Locationdata component helper.
 */
abstract class LocationdataHelper
{

	/**
	*	The Global Admin Event Method.
	**/
	public static function globalEvent($document)
	{
		self::theQueue($document);
	} 

	/**
	* 	The user notice info File Name
	**/
	protected static $usernotice = false;

	/**
	* 	Load the Update Queue Ajax to page
	**/
	public static function theQueue($document, $force = false)
	{
		// set the time
		$now = JFactory::getDate()->toUnix();
		// should an update be forced
		$update = 1; // <-- don’t force update
		if ($force)
		{
			$update = 2; // <-- force update
		}
		$document->addScriptDeclaration("
			jQuery(window).load(function() {
				theQueue();
			});
			function theQueue() {
				var getUrl = '".JURI::root()."administrator/index.php?option=com_locationdata&task=ajax.theQueue&format=json';
				var request = 'token=".JSession::getFormToken()."&time=".$now."&force=".$update."';
				return jQuery.ajax({
					type: 'GET',
					url: getUrl,
					dataType: 'jsonp',
					data: request,
					jsonp: 'callback'
				});
			}
		");
	} 

	/**
	* 	Load the data for IP and Currency 
	**/
	public static function getLocationdata($ip, $protocol, $key, $base, $mode, $string, $value)
	{
		// we still need to add key check
		// if no ip detected try getting the ip or return the default
		if (!$ip)
		{
			if ($found = self::getIP())
			{
				// load the results found
				$ip = $found['ip'];
				$protocol = $found['protocol'];
			}
			else
			{
				// set the default
				$ip = '0.0.0.0';
				$protocol = 1;
			}
		}
		// check that we have protocol
		if (!$protocol)
		{
			if (!$protocol = self::isValidIP($ip, $strict = true))
			{
				// set the default
				$ip = '0.0.0.0';
				$protocol = 1;
			}
		}
		return self::getLocationdataFactory($ip, $protocol)->getInfo((int) $mode, (int) $string, (float) $value, $base);
	}

	public static $forceLocationdataTable = true; 

	public static $defaultComponent = "com_locationdata"; 

	protected static $locationdataFactory = array(); 
	
	protected static function getLocationdataFactory($ip, $protocol)
	{
		$getter = md5($ip);
		if (!isset(self::$locationdataFactory[$getter]))
		{
			// make sure to include the factory file
			include_once 'locationdatafactory.php';
			// first load the factory
			self::$locationdataFactory[$getter] = new LocationdataFactory($ip, $protocol, self::$defaultComponent, self::$forceLocationdataTable);
		}
		return self::$locationdataFactory[$getter];
	}

	protected static $exchangeRates = array(); 

	/**
	 * returns ExchangeRate from #__locationdata_exchange_rate database
	 *
	 * @return string
	 */
	public static function getExchangeRate($from, $fromValue, $to = 'USD', $retry = true)
	{
		// only continue if from and to is set
		if($from && $to)
		{
			if (!isset(self::$exchangeRates[$from.$to]))
			{
				if ($from == $to)
				{
					// it is the same currency so set all one on one
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_ID']		= strtolower($from).'-'.strtolower($from);
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_NAME']		= $from.$from;
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE']		= 1.00;
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_ASK']		= 1.00;
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_BID']		= 1.00;
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_DATE']		= 0;
					// now set the money
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_MONEY']		= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE'], $from);
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_ASK_MONEY']	= self::$exchangeRates[$from.$to]['EXCHANGE_RATE_MONEY'];
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_BID_MONEY']	= self::$exchangeRates[$from.$to]['EXCHANGE_RATE_MONEY'];
					if (!$fromValue)
					{
						$fromValue = 1.00;
					}
					// set the convertion
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM']		= (float) $fromValue;
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM_MONEY']	= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM'], $from);
					// calculate the new value
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_TO']		= (float) $fromValue;
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_TO_MONEY']	= self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM_MONEY'];
					
					return self::$exchangeRates[$from.$to];		
				}
				// the get values
				$get = array(
					'EXCHANGE_RATE_ID'	=> 'a.alias', 
					'EXCHANGE_RATE_NAME'	=> 'a.name', 
					'EXCHANGE_RATE'		=> 'a.rate',
					'EXCHANGE_RATE_ASK'	=> 'a.ask',
					'EXCHANGE_RATE_BID'	=> 'a.bid',
					'EXCHANGE_RATE_DATE'	=> 'a.date_rate');
				// Get a db connection.
				$db = JFactory::getDbo();
				// Create a new query object.
				$query = $db->getQuery(true);
				$query
					->select($db->quoteName(array_values($get),array_keys($get)))
					->from($db->quoteName('#__locationdata_exchange_rate', 'a'))
					->where($db->quoteName('a.from') . ' = '. $db->quote($from))
					->where($db->quoteName('a.to') . ' = '. $db->quote($to))
					->where($db->quoteName('a.published') . ' = 1');
				// Reset the query using our newly populated query object.
				$db->setQuery($query);
				$db->execute();
				if ($db->getNumRows())
				{
					$rates = $db->loadAssoc();
					// load in to active data
					self::$exchangeRates[$from.$to] = array();
					foreach ($rates as $key => $value)
					{
						self::$exchangeRates[$from.$to][$key] = $value;
					}
					// now set the money
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_MONEY']		= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE'], $to);
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_ASK_MONEY']	= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE_ASK'], $to);
					self::$exchangeRates[$from.$to]['EXCHANGE_RATE_BID_MONEY']	= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE_BID'], $to);
				}
				elseif ($retry)
				{
					// if not found try again with default
					$default = JComponentHelper::getParams(self::$defaultComponent)->get('currency', 'USD');
					return self::getExchangeRate($from, $fromValue, $default, false);
				}
			}
			// confirm that it was actually set
			if (isset(self::$exchangeRates[$from.$to]))
			{
				// check if a from value is set
				if (!$fromValue)
				{
					$fromValue = 1.00;
				}
				// set the convertion
				self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM']			= (float) $fromValue;
				self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM_MONEY']		= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM'], $from);
				// calculate the new value
				self::$exchangeRates[$from.$to]['EXCHANGE_RATE_TO']			= bcmul(self::$exchangeRates[$from.$to]['EXCHANGE_RATE_FROM'], self::$exchangeRates[$from.$to]['EXCHANGE_RATE'], 4);
				self::$exchangeRates[$from.$to]['EXCHANGE_RATE_TO_MONEY']		= self::makeMoney(self::$exchangeRates[$from.$to]['EXCHANGE_RATE_TO'], $to);
				// load the currency pair name
				self::$exchangeRates[$from.$to]['CURRENCY_PAIR']				= $from.$to;
				// load the currency names
				self::$exchangeRates[$from.$to]['FROM_CURRENCY_CODE_THREE']			= $from;
				self::$exchangeRates[$from.$to]['TO_CURRENCY_CODE_THREE']			= $to;
				// return the exchange rate
				return self::$exchangeRates[$from.$to];
			}
		}
		return false;
	}

	/**
	*  get ip address
	**/
	public static function getIP($plusProtocol = true, $useProxy = 'global')
	{
		// check if we should use proxy
		$useProxy = ($useProxy == 'global') ? JComponentHelper::getParams('com_locationdata')->get('use_proxy', true) :  ( ($useProxy) ? true : false);
		// get the input values
		$server = JFactory::getApplication()->input->server;
		// get remote address (most secure IP since it is set by the server)
		$defaultIP = $server->get('REMOTE_ADDR', false, 'CMD');
		if ($useProxy)
		{
			// proxy options array
			$proxiesDefault = array(
				'HTTP_X_REAL_IP',
				'HTTP_CLIENT_IP',
				'HTTP_TRUE_CLIENT_IP',
				'HTTP_X_FWD_IP_ADDR',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'HTTP_VIA',
				'HTTP_X_COMING_FROM',
				'HTTP_COMING_FROM'
			);
			$proxies = (array) JComponentHelper::getParams('com_locationdata')->get('proxies', $proxiesDefault);

			foreach ($proxies as $proxy)
			{
				if ($ip =  $server->get($proxy, false, 'STRING'))
				{
					// let's see if there are multiple IPs
					if (strpos($ip, ',') !== false)
					{
						$tmp = explode(', ', $ip);
						// grab the first IP
						$ip = reset($tmp);
						// no longer need this
						unset($tmp);
					}
					// check if valid IP in strict mode
					if ($protocol = self::isValidIP($ip, true))
					{
						// return proxy IP (not that secure)
						if ($plusProtocol)
						{
							return array('ip' => $ip, 'protocol' => $protocol);
						}
						else
						{
							return $ip;
						}
					}
				}
			}
		}
		// check if vailid IP
		if ($protocol = self::isValidIP($defaultIP, true))
		{
			// return default IP (most secure)
			if ($plusProtocol)
			{
				return  array('ip' => $defaultIP, 'protocol' => $protocol);
			}
			else
			{
				return $defaultIP;
			}
		}
		return false;
	}
			
	/**
	* Tests if supplied IP address is IPv4 of IPv6 and valid.
	**/
	public static function isValidIP($ip, $strict = true)
	{
		if ($strict)
		{
			if (defined('FILTER_VALIDATE_IP') && defined('FILTER_FLAG_IPV4') && defined('FILTER_FLAG_IPV6') && defined('FILTER_FLAG_NO_PRIV_RANGE') && defined('FILTER_FLAG_NO_RES_RANGE'))
			{
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
				{
					return 4;
				}
				elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
				{
					return 6;
				}
			}
			elseif (strpos($ip, '.') !== false && strpos($ip, ':') === false  && self::validateV4($ip))
			{
				return 4;
			}
			elseif (strpos($ip, ':') !== false) // most still add validation for ipv6
			{
				return 6;
			}
			return false;
		}
		else
		{			
			if (defined('FILTER_VALIDATE_IP') && defined('FILTER_FLAG_IPV4') && defined('FILTER_FLAG_IPV6'))
			{
				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
				{
					return 4;
				}
				elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
				{
					return 6;
				}
			}
			elseif (strpos($ip, '.') !== false && strpos($ip, ':') === false)
			{
				return 4;
			}
			elseif (strpos($ip, ':') !== false)
			{
				return 6;
			}
			return false;
		}
	}

	/**
	 * Ensures an ip address is both a valid IP and does not fall within a private network range.
	 *
	 * Thanks to: https://gist.github.com/cballou/2201933
	 */
	protected static function validateV4($ip)
	{
		// generate ipv4 network address
		$ip = ip2long($ip);
		// if the ip is set and not equivalent to 255.255.255.255
		if ($ip !== false && $ip !== -1)
		{
			// make sure to get unsigned long representation of ip
			// due to discrepancies between 32 and 64 bit OSes and
			// signed numbers (ints default to signed in PHP)
			$ip = sprintf('%u', $ip);
			// do private network range checking
			if ($ip >= 0 && $ip <= 50331647) return false;
			if ($ip >= 167772160 && $ip <= 184549375) return false;
			if ($ip >= 2130706432 && $ip <= 2147483647) return false;
			if ($ip >= 2851995648 && $ip <= 2852061183) return false;
			if ($ip >= 2886729728 && $ip <= 2887778303) return false;
			if ($ip >= 3221225984 && $ip <= 3221226239) return false;
			if ($ip >= 3232235520 && $ip <= 3232301055) return false;
			if ($ip >= 4294967040) return false;
		}
		return true;
	}			

	/**
	* @param float $amount
	**/
	public static function addCurrency($amount, $codethree = false)
	{
		return self::makeMoney($amount, $codethree);
	}

	protected static $currencyDetails = array();

	public static function getCurrencyDetails($codethree = false)
	{
		// check if currency codethree is set
		if (!$codethree)
		{
			// get the main currency
			$codethree = JComponentHelper::getParams(self::$defaultComponent)->get('currency', 'USD');
		}
		// return cached data if set
		if ($codethree && !isset(self::$currencyDetails[$codethree]))
		{
			// Get a db connection.
			$db = JFactory::getDbo();
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(
				array(	'a.id','a.name','a.codethree','a.numericcode','a.symbol','a.thousands','a.decimalplace',
					'a.decimalsymbol','a.positivestyle','a.negativestyle'),
				array(	'currency_id','currency_name','currency_codethree','currency_numericcode','currency_symbol',
					'currency_thousands','currency_decimalplace','currency_decimalsymbol','currency_positivestyle',
					'currency_negativestyle')));
			$query->from($db->quoteName('#__locationdata_currency', 'a'));
			if (is_numeric($codethree))
			{
				$query->where($db->quoteName('a.id') . ' = '. (int) $codethree);
			}
			elseif (strlen($codethree) == 3)
			{
				$query->where($db->quoteName('a.codethree') . ' = '.$db->quote($codethree));
			}
			else
			{
				$query->where($db->quoteName('a.codethree') . ' = '.$db->quote('NONE'));
			}
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				self::$currencyDetails[$codethree] = $db->loadObject();
			}
		}
		// make sure it has been set
		if (isset(self::$currencyDetails[$codethree]))
		{
			return self::$currencyDetails[$codethree];
		}
		return false;
	}
			
	/**
	 * @param $number
	 * @param bool $currency
	 * @return mixed|number
	 */
	public static function makeMoney($number, $currency = false)
	{
		// first check if we have a number
		if (is_numeric($number))
		{
			// make sure to include the negative finder file
			include_once 'negativefinder.php';
			// check if the number is negative
			$negativeFinderObj = new LocationdataNegativeFinder(new LocationdataExpression("$number"));
			$negative = $negativeFinderObj->isItNegative() ? TRUE : FALSE;
		}
		else
		{
			// just return the string
			return $number;
		}
		// not setup the currency
		if (self::checkObject($currency))
		{
			if(!isset($currency->currency_positivestyle) || !isset($currency->currency_negativestyle) || !isset($currency->currency_decimalplace) || !isset($currency->currency_decimalsymbol) || !isset($currency->currency_symbol))
			{
				if (isset($currency->currency_id))
				{
					$currency = self::getCurrencyDetails($currency->currency_id);
				}
				elseif (isset($currency->id))
				{
					$currency = self::getCurrencyDetails($currency->id);
				}
				else
				{
					$currency = self::getCurrencyDetails();
				}
			}
		}
		else
		{
			$currency = self::getCurrencyDetails($currency);
		}
		// set the number to currency
		if (self::checkObject($currency))
		{
			if (!$negative)
			{
				$format = $currency->currency_positivestyle;
				$sign = '+';
			}
			else
			{
				$format = $currency->currency_negativestyle;
				$sign = '-';
				$number = abs($number);
			}
			$setupNumber = number_format((float)$number, (int)$currency->currency_decimalplace, $currency->currency_decimalsymbol, ' '); //$currency->currency_thousands TODO);
			$search = array('{sign}', '{number}', '{symbol}');
			$replace = array($sign, $setupNumber, $currency->currency_symbol);
			$moneyMade = str_replace ($search,$replace,$format);

			return $moneyMade;
		}
		return $number;
	}			 
			
	/**
	 *	Change to nice fancy date
	 */
	public static function fancyDate($date)
	{
		if (!self::isValidTimeStamp($date))
		{
			$date = strtotime($date);
		}
		return date('jS \o\f F Y',$date);
	}

	/**
	 *	Change to nice fancy day time and date
	 */
	public static function fancyDayTimeDate($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('D ga jS \o\f F Y',$time);
	}

	/**
	 *	Change to nice fancy time and date
	 */
	public static function fancyDateTime($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('(G:i) jS \o\f F Y',$time);
	}

	/**
	 *	Change to nice hour:minutes time
	 */
	public static function fancyTime($time)
	{
		if (!self::isValidTimeStamp($time))
		{
			$time = strtotime($time);
		}
		return date('G:i',$time);
	}

	/**
	 *	Check if string is a valid time stamp
	 */
	public static function isValidTimeStamp($timestamp)
	{
		return ((int) $timestamp === $timestamp)
		&& ($timestamp <= PHP_INT_MAX)
		&& ($timestamp >= ~PHP_INT_MAX);
	}
			 
	public static function getFilePath($type, $name = 'listing', $key = '', $fileType = '.json', $PATH = JPATH_COMPONENT_SITE)
	{
		if (!self::checkString(self::${$type.$name}))
		{
			// Get local key
			$localkey = self::getLocalKey();
			// set the name
			$fileName = md5($type.$name.$localkey.$key);
			// set file path			
			self::${$type.$name} = $PATH.'/helpers/'.$fileName.$fileType;
		}
		// return the path
		return self::${$type.$name};
	}

	/**
	* 	get the localkey
	**/
	protected static $localkey = false;
	
	public static function getLocalKey()
	{
		if (!self::$localkey)
		{
			// get the main key
			self::$localkey = md5(JComponentHelper::getParams('com_locationdata')->get('basic', 'localKey34fdWEkl'));
		}
		return self::$localkey;
	}
	/**
	*	Load the Component xml manifest.
	**/
        public static function manifest()
	{
                $manifestUrl = JPATH_ADMINISTRATOR."/components/com_locationdata/locationdata.xml";
                return simplexml_load_file($manifestUrl);
	}

	/**
	*	Load the Contributors details.
	**/
	public static function getContributors()
	{
		// get params
		$params	= JComponentHelper::getParams('com_locationdata');
		// start contributors array
		$contributors = array();
		// get all Contributors (max 20)
		$searchArray = range('0','20');
		foreach($searchArray as $nr)
                {
			if ((NULL !== $params->get("showContributor".$nr)) && ($params->get("showContributor".$nr) == 1 || $params->get("showContributor".$nr) == 3))
                        {
				// set link based of selected option
				if($params->get("useContributor".$nr) == 1)
                                {
					$link_front = '<a href="mailto:'.$params->get("emailContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
                                elseif($params->get("useContributor".$nr) == 2)
                                {
					$link_front = '<a href="'.$params->get("linkContributor".$nr).'" target="_blank">';
					$link_back = '</a>';
				}
                                else
                                {
					$link_front = '';
					$link_back = '';
				}
				$contributors[$nr]['title']	= self::htmlEscape($params->get("titleContributor".$nr));
				$contributors[$nr]['name']	= $link_front.self::htmlEscape($params->get("nameContributor".$nr)).$link_back;
			}
		}
		return $contributors;
	}

	/**
	*	Can be used to build help urls.
	**/
	public static function getHelpUrl($view)
	{
		return false;
	}

	/**
	*	Configure the Linkbar.
	**/
	public static function addSubmenu($submenu)
	{
                // load user for access menus
                $user = JFactory::getUser();
                // load the submenus to sidebar
                JHtmlSidebar::addEntry(JText::_('COM_LOCATIONDATA_SUBMENU_DASHBOARD'), 'index.php?option=com_locationdata&view=locationdata', $submenu === 'locationdata');
		if ($user->authorise('country.access', 'com_locationdata') && $user->authorise('country.submenu', 'com_locationdata'))
		{
			JHtmlSidebar::addEntry(JText::_('COM_LOCATIONDATA_SUBMENU_COUNTRIES'), 'index.php?option=com_locationdata&view=countries', $submenu === 'countries');
		}
		if ($user->authorise('currency.access', 'com_locationdata') && $user->authorise('currency.submenu', 'com_locationdata'))
		{
			JHtmlSidebar::addEntry(JText::_('COM_LOCATIONDATA_SUBMENU_CURRENCIES'), 'index.php?option=com_locationdata&view=currencies', $submenu === 'currencies');
		}
		if ($user->authorise('exchange_rate.access', 'com_locationdata') && $user->authorise('exchange_rate.submenu', 'com_locationdata'))
		{
			JHtmlSidebar::addEntry(JText::_('COM_LOCATIONDATA_SUBMENU_EXCHANGE_RATES'), 'index.php?option=com_locationdata&view=exchange_rates', $submenu === 'exchange_rates');
		}
		if ($user->authorise('ip_table.access', 'com_locationdata') && $user->authorise('ip_table.submenu', 'com_locationdata'))
		{
			JHtmlSidebar::addEntry(JText::_('COM_LOCATIONDATA_SUBMENU_IP_TABLES'), 'index.php?option=com_locationdata&view=ip_tables', $submenu === 'ip_tables');
		}
		if ($user->authorise('update_data.submenu', 'com_locationdata'))
		{
			JHtmlSidebar::addEntry(JText::_('COM_LOCATIONDATA_SUBMENU_UPDATEDATA'), 'index.php?option=com_locationdata&view=updatedata', $submenu === 'updatedata');
		}
	} 

	/**
	* 	UIKIT Component Classes
	**/
	public static $uk_components = array(
			'data-uk-grid' => array(
				'grid' ),
			'uk-accordion' => array(
				'accordion' ),
			'uk-autocomplete' => array(
				'autocomplete' ),
			'data-uk-datepicker' => array(
				'datepicker' ),
			'uk-form-password' => array(
				'form-password' ),
			'uk-form-select' => array(
				'form-select' ),
			'data-uk-htmleditor' => array(
				'htmleditor' ),
			'data-uk-lightbox' => array(
				'lightbox' ),
			'uk-nestable' => array(
				'nestable' ),
			'UIkit.notify' => array(
				'notify' ),
			'data-uk-parallax' => array(
				'parallax' ),
			'uk-search' => array(
				'search' ),
			'uk-slider' => array(
				'slider' ),
			'uk-slideset' => array(
				'slideset' ),
			'uk-slideshow' => array(
				'slideshow',
				'slideshow-fx' ),
			'uk-sortable' => array(
				'sortable' ),
			'data-uk-sticky' => array(
				'sticky' ),
			'data-uk-timepicker' => array(
				'timepicker' ),
			'data-uk-tooltip' => array(
				'tooltip' ),
			'uk-placeholder' => array(
				'placeholder' ),
			'uk-dotnav' => array(
				'dotnav' ),
			'uk-slidenav' => array(
				'slidenav' ),
			'uk-form' => array(
				'form-advanced' ),
			'uk-progress' => array(
				'progress' ),
			'upload-drop' => array(
				'upload', 'form-file' )
			);
	
	/**
	* 	Add UIKIT Components
	**/
	public static $uikit = false;

	/**
	* 	Get UIKIT Components
	**/
	public static function getUikitComp($content,$classes = array())
	{
		if (strpos($content,'class="uk-') !== false)
		{
			// reset
			$temp = array();
			foreach (self::$uk_components as $looking => $add)
			{
				if (strpos($content,$looking) !== false)
				{
					$temp[] = $looking;
				}
			}
			// make sure uikit is loaded to config
			if (strpos($content,'class="uk-') !== false)
			{
				self::$uikit = true;
			}
			// sorter
			if (self::checkArray($temp))
			{
				// merger
				if (self::checkArray($classes))
				{
					$newTemp = array_merge($temp,$classes);
					$temp = array_unique($newTemp);
				}
				return $temp;
			}
		}	
		if (self::checkArray($classes))
		{
			return $classes;
		}
		return false;
	} 

	/**
	 * Prepares the xml document
	 */
	public static function xls($rows,$fileName = null,$title = null,$subjectTab = null,$creator = 'Vast Development Method',$description = null,$category = null,$keywords = null,$modified = null)
	{
		// set the user
		$user = JFactory::getUser();
		
		// set fieldname if not set
		if (!$fileName)
		{
			$fileName = 'exported_'.JFactory::getDate()->format('jS_F_Y');
		}
		// set modiefied if not set
		if (!$modified)
		{
			$modified = $user->name;
		}
		// set title if not set
		if (!$title)
		{
			$title = 'Book1';
		}
		// set tab name if not set
		if (!$subjectTab)
		{
			$subjectTab = 'Sheet1';
		}
		
		// make sure the file is loaded		
		JLoader::import('PHPExcel', JPATH_COMPONENT_ADMINISTRATOR . '/helpers');
		
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		
		// Set document properties
		$objPHPExcel->getProperties()->setCreator($creator)
									 ->setCompany('Vast Development Method')
									 ->setLastModifiedBy($modified)
									 ->setTitle($title)
									 ->setSubject($subjectTab);
		if (!$description)
		{
			$objPHPExcel->getProperties()->setDescription($description);
		}
		if (!$keywords)
		{
			$objPHPExcel->getProperties()->setKeywords($keywords);
		}
		if (!$category)
		{
			$objPHPExcel->getProperties()->setCategory($category);
		}
		
		// Some styles
		$headerStyles = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '1171A3'),
				'size'  => 12,
				'name'  => 'Verdana'
		));
		$sideStyles = array(
			'font'  => array(
				'bold'  => true,
				'color' => array('rgb' => '444444'),
				'size'  => 11,
				'name'  => 'Verdana'
		));
		$normalStyles = array(
			'font'  => array(
				'color' => array('rgb' => '444444'),
				'size'  => 11,
				'name'  => 'Verdana'
		));
		
		// Add some data
		if (self::checkArray($rows))
		{
			$i = 1;
			foreach ($rows as $array){
				$a = 'A';
				foreach ($array as $value){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($a.$i, $value);
					if ($i == 1){
						$objPHPExcel->getActiveSheet()->getColumnDimension($a)->setAutoSize(true);
						$objPHPExcel->getActiveSheet()->getStyle($a.$i)->applyFromArray($headerStyles);
						$objPHPExcel->getActiveSheet()->getStyle($a.$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					} elseif ($a === 'A'){
						$objPHPExcel->getActiveSheet()->getStyle($a.$i)->applyFromArray($sideStyles);
					} else {
						$objPHPExcel->getActiveSheet()->getStyle($a.$i)->applyFromArray($normalStyles);
					}
					$a++;
				}
				$i++;
			}
		}
		else
		{
			return false;
		}
		
		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle($subjectTab);
		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		// Redirect output to a client's web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$fileName.'.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');
		
		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		jexit();
	}
	
	/**
	* Get CSV Headers
	*/
	public static function getFileHeaders($dataType)
	{		
		// make sure these files are loaded		
		JLoader::import('PHPExcel', JPATH_COMPONENT_ADMINISTRATOR . '/helpers');
		JLoader::import('ChunkReadFilter', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/PHPExcel/Reader');
		// get session object
		$session	= JFactory::getSession();
		$package	= $session->get('package', null);
		$package	= json_decode($package, true);
		// set the headers
		if(isset($package['dir']))
		{
			$chunkFilter = new PHPExcel_Reader_chunkReadFilter();
			// only load first three rows
			$chunkFilter->setRows(2,1);
			// identify the file type
			$inputFileType = PHPExcel_IOFactory::identify($package['dir']);
			// create the reader for this file type
			$excelReader = PHPExcel_IOFactory::createReader($inputFileType);
			// load the limiting filter
			$excelReader->setReadFilter($chunkFilter);
			$excelReader->setReadDataOnly(true);
			// load the rows (only first three)
			$excelObj = $excelReader->load($package['dir']);
			$headers = array();
			foreach ($excelObj->getActiveSheet()->getRowIterator() as $row)
			{
				if($row->getRowIndex() == 1)
				{
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);
					foreach ($cellIterator as $cell)
					{
						if (!is_null($cell))
						{
							$headers[$cell->getColumn()] = $cell->getValue();
						}
					}
					$excelObj->disconnectWorksheets();
					unset($excelObj);
					break;
				}
			}
			return $headers;
		}
		return false;
	}

	public static function getVar($table, $where = null, $whereString = 'user', $what = 'id', $operator = '=', $main = 'locationdata')
	{
		if(!$where)
		{
			$where = JFactory::getUser()->id;
		}
		// Get a db connection.
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array($what)));
		if (empty($table))
		{
			$query->from($db->quoteName('#__'.$main));
		}
		else
		{
			$query->from($db->quoteName('#__'.$main.'_'.$table));
		}
		if (is_numeric($where))
		{
			$query->where($db->quoteName($whereString) . ' '.$operator.' '.(int) $where);
		}
		elseif (is_string($where))
		{
			$query->where($db->quoteName($whereString) . ' '.$operator.' '. $db->quote((string)$where));
		}
		else
		{
			return false;
		}
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows())
		{
			return $db->loadResult();
		}
		return false;
	}

	public static function getVars($table, $where = null, $whereString = 'user', $what = 'id', $operator = 'IN', $main = 'locationdata', $unique = true)
	{
		if(!$where)
		{
			$where = JFactory::getUser()->id;
		}

		if (!self::checkArray($where) && $where > 0)
		{
			$where = array($where);
		}

		if (self::checkArray($where))
		{
			// prep main <-- why? well if $main='' is empty then $table can be categories or users
			if (self::checkString($main))
			{
				$main = '_'.ltrim($main, '_');
			}
			// Get a db connection.
			$db = JFactory::getDbo();
			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array($what)));
			if (empty($table))
			{
				$query->from($db->quoteName('#__'.$main));
			}
			else
			{
				$query->from($db->quoteName('#_'.$main.'_'.$table));
			}
			$query->where($db->quoteName($whereString) . ' '.$operator.' (' . implode(',',$where) . ')');
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				if ($unique)
				{
					return array_unique($db->loadColumn());
				}
				return $db->loadColumn();
			}
		}
		return false;
	}

	public static function jsonToString($value, $sperator = ", ", $table = null)
	{
                // check if string is JSON
                $result = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE)
		{
			// is JSON
			if (self::checkArray($result))
			{
				if (self::checkString($table))
				{
					$names = array();
					foreach ($result as $val)
					{
						if ($name = self::getVar($table, $val, 'id', 'name'))
						{
							$names[] = $name;
						}
					}
					if (self::checkArray($names))
					{
						return (string) implode($sperator,$names);
					}	
				}
				return (string) implode($sperator,$result);
			}
                        return (string) json_decode($value);
                }
                return $value;
        }

	public static function isPublished($id,$type)
	{
		if ($type == 'raw')
                {
			$type = 'item';
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('a.published'));
		$query->from('#__locationdata_'.$type.' AS a');
		$query->where('a.id = '. (int) $id);
		$query->where('a.published = 1');
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
                {
			return true;
		}
		return false;
	}

	public static function getGroupName($id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(array('a.title'));
		$query->from('#__usergroups AS a');
		$query->where('a.id = '. (int) $id);
		$db->setQuery($query);
		$db->execute();
		$found = $db->getNumRows();
		if($found)
                {
			return $db->loadResult();
		}
		return $id;
	}

        /**
	*	Get the actions permissions
	**/
        public static function getActions($view,&$record = null,$views = null)
	{
		jimport('joomla.access.access');

		$user	= JFactory::getUser();
		$result	= new JObject;
		$view	= self::safeString($view);
                if (self::checkString($views))
                {
			$views = self::safeString($views);
                }
		// get all actions from component
		$actions = JAccess::getActions('com_locationdata', 'component');
                // set acctions only set in component settiongs
                $componentActions = array('core.admin','core.manage','core.options','core.export');
		// loop the actions and set the permissions
		foreach ($actions as $action)
                {
			// set to use component default
			$fallback= true;
			if (self::checkObject($record) && isset($record->id) && $record->id > 0 && !in_array($action->name,$componentActions))
			{
				// The record has been set. Check the record permissions.
				$permission = $user->authorise($action->name, 'com_locationdata.'.$view.'.' . (int) $record->id);
				if (!$permission) // TODO removed && !is_null($permission)
				{
					if ($action->name == 'core.edit' || $action->name == $view.'.edit')
					{
						if ($user->authorise('core.edit.own', 'com_locationdata.'.$view.'.' . (int) $record->id))
						{
							// If the owner matches 'me' then allow.
							if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
							{
								$result->set($action->name, true);
								// set not to use component default
								$fallback= false;
							}
							else
							{
								$result->set($action->name, false);
								// set not to use component default
								$fallback= false;
							}
						}
						elseif ($user->authorise($view.'edit.own', 'com_locationdata.'.$view.'.' . (int) $record->id))
						{
							// If the owner matches 'me' then allow.
							if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
							{
								$result->set($action->name, true);
								// set not to use component default
								$fallback= false;
							}
							else
							{
								$result->set($action->name, false);
								// set not to use component default
								$fallback= false;
							}
						}
						elseif ($user->authorise('core.edit.own', 'com_locationdata'))
						{
							// If the owner matches 'me' then allow.
							if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
							{
								$result->set($action->name, true);
								// set not to use component default
								$fallback= false;
							}
							else
							{
								$result->set($action->name, false);
								// set not to use component default
								$fallback= false;
							}
						}
						elseif ($user->authorise($view.'edit.own', 'com_locationdata'))
						{
							// If the owner matches 'me' then allow.
							if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
							{
								$result->set($action->name, true);
								// set not to use component default
								$fallback= false;
							}
							else
							{
								$result->set($action->name, false);
								// set not to use component default
								$fallback= false;
							}
						}
					}
				}
				elseif (self::checkString($views) && isset($record->catid) && $record->catid > 0)
				{
                                        // make sure we use the core. action check for the categories
                                        if (strpos($action->name,$view) !== false && strpos($action->name,'core.') === false ) {
                                                $coreCheck		= explode('.',$action->name);
                                                $coreCheck[0]	= 'core';
                                                $categoryCheck	= implode('.',$coreCheck);
                                        }
                                        else
                                        {
                                                $categoryCheck = $action->name;
                                        }
                                        // The record has a category. Check the category permissions.
					$catpermission = $user->authorise($categoryCheck, 'com_locationdata.'.$views.'.category.' . (int) $record->catid);
					if (!$catpermission && !is_null($catpermission))
					{
						if ($action->name == 'core.edit' || $action->name == $view.'.edit')
						{
							if ($user->authorise('core.edit.own', 'com_locationdata.'.$views.'.category.' . (int) $record->catid))
							{
								// If the owner matches 'me' then allow.
								if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
								{
									$result->set($action->name, true);
									// set not to use component default
									$fallback= false;
								}
								else
								{
									$result->set($action->name, false);
									// set not to use component default
									$fallback= false;
								}
							}
							elseif ($user->authorise($view.'edit.own', 'com_locationdata.'.$views.'.category.' . (int) $record->catid))
							{
								// If the owner matches 'me' then allow.
								if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
								{
									$result->set($action->name, true);
									// set not to use component default
									$fallback= false;
								}
								else
								{
									$result->set($action->name, false);
									// set not to use component default
									$fallback= false;
								}
							}
							elseif ($user->authorise('core.edit.own', 'com_locationdata'))
							{
								// If the owner matches 'me' then allow.
								if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
								{
									$result->set($action->name, true);
									// set not to use component default
									$fallback= false;
								}
								else
								{
									$result->set($action->name, false);
									// set not to use component default
									$fallback= false;
								}
							}
							elseif ($user->authorise($view.'edit.own', 'com_locationdata'))
							{
								// If the owner matches 'me' then allow.
								if (isset($record->created_by) && $record->created_by > 0 && ($record->created_by == $user->id))
								{
									$result->set($action->name, true);
									// set not to use component default
									$fallback= false;
								}
								else
								{
									$result->set($action->name, false);
									// set not to use component default
									$fallback= false;
								}
							}
						}
					}
				}
			}
			// if allowed then fallback on component global settings
			if ($fallback)
			{
				$result->set($action->name, $user->authorise($action->name, 'com_locationdata'));
			}
		}
		return $result;
	}

	/**
	*	Get any component's model
	**/
	public static function getModel($name, $path = JPATH_COMPONENT_ADMINISTRATOR, $component = 'locationdata')
	{
		// load the model file
		JModelLegacy::addIncludePath( $path . '/models' );
		// get instance
		$model = JModelLegacy::getInstance( $name, $component.'Model' );
		// if model not found
		if ($model == false)
		{
			// build class name
			$class = $prefix.$name;
			// initilize the model
			new $class();
			$model = JModelLegacy::getInstance($name, $prefix);
		}
		return $model;
	}
	
	/**
	*	Add to asset Table
	*/
	public static function setAsset($id,$table)
	{
		$parent = JTable::getInstance('Asset');
		$parent->loadByName('com_locationdata');
		
		$parentId = $parent->id;
		$name     = 'com_locationdata.'.$table.'.'.$id;
		$title    = '';

		$asset = JTable::getInstance('Asset');
		$asset->loadByName($name);

		// Check for an error.
		$error = $asset->getError();

		if ($error)
		{
			return false;
		}
		else
		{
			// Specify how a new or moved node asset is inserted into the tree.
			if ($asset->parent_id != $parentId)
			{
				$asset->setLocation($parentId, 'last-child');
			}

			// Prepare the asset to be stored.
			$asset->parent_id = $parentId;
			$asset->name      = $name;
			$asset->title     = $title;
			// get the default asset rules
			$rules = self::getDefaultAssetRules('com_locationdata',$table);
			if ($rules instanceof JAccessRules)
			{
				$asset->rules = (string) $rules;
			}

			if (!$asset->check() || !$asset->store())
			{
				JFactory::getApplication()->enqueueMessage($asset->getError(), 'warning');
				return false;
			}
			else
			{
				// Create an asset_id or heal one that is corrupted.
				$object = new stdClass();

				// Must be a valid primary key value.
				$object->id = $id;
				$object->asset_id = (int) $asset->id;

				// Update their asset_id to link to the asset table.
				return JFactory::getDbo()->updateObject('#__locationdata_'.$table, $object, 'id');
			}
		}
		return false;
	}
	
	/**
	 *	Gets the default asset Rules for a component/view.
	 */
	protected static function getDefaultAssetRules($component,$view)
	{
		// Need to find the asset id by the name of the component.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . ' = ' . $db->quote($component));
		$db->setQuery($query);
		$db->execute();
		if ($db->loadRowList())
		{
			// asset alread set so use saved rules
			$assetId = (int) $db->loadResult();
			$result =  JAccess::getAssetRules($assetId);
			if ($result instanceof JAccessRules)
			{
				$_result = (string) $result;
				$_result = json_decode($_result);
				foreach ($_result as $name => &$rule)
				{
					$v = explode('.', $name);
					if ($view !== $v[0])
					{
						// remove since it is not part of this view
						unset($_result->$name);
					}
					else
					{
						// clear the value since we inherit
						$rule = array();
					}
				}
				// check if there are any view values remaining
				if (count($_result))
				{
					$_result = json_encode($_result);
					$_result = array($_result);
					// Instantiate and return the JAccessRules object for the asset rules.
					$rules = new JAccessRules($_result);

					return $rules;
				}
				return $result;
			}
		}
		return JAccess::getAssetRules(0);
	}

	public static function renderBoolButton()
	{
		$args = func_get_args();

		// get the radio element
		$button = JFormHelper::loadFieldType('radio');

		// setup the properties
		$name	 	= self::htmlEscape($args[0]);
		$additional = isset($args[1]) ? (string) $args[1] : '';
		$value		= $args[2];
		$yes 	 	= isset($args[3]) ? self::htmlEscape($args[3]) : 'JYES';
		$no 	 	= isset($args[4]) ? self::htmlEscape($args[4]) : 'JNO';

		// prepare the xml
		$element = new SimpleXMLElement('<field name="'.$name.'" type="radio" class="btn-group"><option '.$additional.' value="0">'.$no.'</option><option '.$additional.' value="1">'.$yes.'</option></field>');

		// run
		$button->setup($element, $value);

		return $button->input;

	}
	
	public static function checkJson($string)
	{
		if (self::checkString($string))
		{
			json_decode($string);
			return (json_last_error() === JSON_ERROR_NONE);
		}
		return false;
	}

	public static function checkObject($object)
	{
		if (isset($object) && is_object($object) && count($object) > 0)
		{
			return true;
		}
		return false;
	}

	public static function checkArray($array, $removeEmptyString = false)
	{
		if (isset($array) && is_array($array) && count($array) > 0)
		{
			// also make sure the empty strings are removed
			if ($removeEmptyString)
			{
				foreach ($array as $key => $string)
				{
					if (empty($string))
					{
						unset($array[$key]);
					}
				}
				return self::checkArray($array, false);
			}
			return true;
		}
		return false;
	}

	public static function checkString($string)
	{
		if (isset($string) && is_string($string) && strlen($string) > 0)
		{
			return true;
		}
		return false;
	}

	public static function mergeArrays($arrays)
	{
		if(self::checkArray($arrays))
		{
			$arrayBuket = array();
			foreach ($arrays as $array)
			{
				if (self::checkArray($array))
				{
					$arrayBuket = array_merge($arrayBuket, $array);
				}
			}
			return $arrayBuket;
		}
		return false;
	}

	// typo sorry!
	public static function sorten($string, $length = 40, $addTip = true)
	{
		return self::shorten($string, $length, $addTip);
	}

	public static function shorten($string, $length = 40, $addTip = true)
	{
		if (self::checkString($string))
		{
			$initial = strlen($string);
			$words = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
			$words_count = count($words);

			$word_length = 0;
			$last_word = 0;
			for (; $last_word < $words_count; ++$last_word)
			{
				$word_length += strlen($words[$last_word]);
				if ($word_length > $length)
				{
					break;
				}
			}

			$newString	= implode(array_slice($words, 0, $last_word));
			$final	= strlen($newString);
			if ($initial != $final && $addTip)
			{
				$title = self::shorten($string, 400 , false);
				return '<span class="hasTip" title="'.$title.'" style="cursor:help">'.trim($newString).'...</span>';
			}
			elseif ($initial != $final && !$addTip)
			{
				return trim($newString).'...';
			}
		}
		return $string;
	}

	public static function safeString($string, $type = 'L', $spacer = '_', $replaceNumbers = true)
	{
		if ($replaceNumbers === true)
		{
			// remove all numbers and replace with english text version (works well only up to millions)
			$string = self::replaceNumbers($string);
		}
		// 0nly continue if we have a string
                if (self::checkString($string))
                {
			// create file name without the extention that is safe
			if ($type === 'filename')
			{
				// make sure VDM is not in the string
				$string = str_replace('VDM', 'vDm', $string);
				// Remove anything which isn't a word, whitespace, number
				// or any of the following caracters -_()
				// If you don't need to handle multi-byte characters
				// you can use preg_replace rather than mb_ereg_replace
				// Thanks @Łukasz Rysiak!
				// $string = mb_ereg_replace("([^\w\s\d\-_\(\)])", '', $string);
				$string = preg_replace("([^\w\s\d\-_\(\)])", '', $string);
				// http://stackoverflow.com/a/2021729/1429677
				return preg_replace('/\s+/', ' ', $string);
			}
			// remove all other characters
			$string = trim($string);
			$string = preg_replace('/'.$spacer.'+/', ' ', $string);
			$string = preg_replace('/\s+/', ' ', $string);
			$string = preg_replace("/[^A-Za-z ]/", '', $string);
			// select final adaptations
			if ($type === 'L' || $type === 'strtolower')
                        {
                                // replace white space with underscore
                                $string = preg_replace('/\s+/', $spacer, $string);
                                // default is to return lower
                                return strtolower($string);
                        }
			elseif ($type === 'W')
			{
				// return a string with all first letter of each word uppercase(no undersocre)
				return ucwords(strtolower($string));
			}
			elseif ($type === 'w' || $type === 'word')
			{
				// return a string with all lowercase(no undersocre)
				return strtolower($string);
			}
			elseif ($type === 'Ww' || $type === 'Word')
			{
				// return a string with first letter of the first word uppercase and all the rest lowercase(no undersocre)
				return ucfirst(strtolower($string));
			}
			elseif ($type === 'WW' || $type === 'WORD')
			{
				// return a string with all the uppercase(no undersocre)
				return strtoupper($string);
			}
                        elseif ($type === 'U' || $type === 'strtoupper')
                        {
                                // replace white space with underscore
                                $string = preg_replace('/\s+/', $spacer, $string);
                                // return all upper
                                return strtoupper($string);
                        }
                        elseif ($type === 'F' || $type === 'ucfirst')
                        {
                                // replace white space with underscore
                                $string = preg_replace('/\s+/', $spacer, $string);
                                // return with first caracter to upper
                                return ucfirst(strtolower($string));
                        }
                        elseif ($type === 'cA' || $type === 'cAmel' || $type === 'camelcase')
			{
				// convert all words to first letter uppercase
				$string = ucwords(strtolower($string));
				// remove white space
				$string = preg_replace('/\s+/', '', $string);
				// now return first letter lowercase
				return lcfirst($string);
			}
                        // return string
                        return $string;
                }
                // not a string
                return '';
	}

        public static function htmlEscape($var, $charset = 'UTF-8', $shorten = false, $length = 40)
	{
		if (self::checkString($var))
		{
			$filter = new JFilterInput();
			$string = $filter->clean(html_entity_decode(htmlentities($var, ENT_COMPAT, $charset)), 'HTML');
			if ($shorten)
			{
                                return self::shorten($string,$length);
			}
			return $string;
                }
		else
		{
			return '';
                }
	}

	public static function replaceNumbers($string)
	{
		// set numbers array
		$numbers = array();
		// first get all numbers
		preg_match_all('!\d+!', $string, $numbers);
		// check if we have any numbers
		if (isset($numbers[0]) && self::checkArray($numbers[0]))
		{
			foreach ($numbers[0] as $number)
			{
				$searchReplace[$number] = self::numberToString((int)$number);
			}
			// now replace numbers in string
			$string = str_replace(array_keys($searchReplace), array_values($searchReplace),$string);
			// check if we missed any, strange if we did.
			return self::replaceNumbers($string);
		}
		// return the string with no numbers remaining.
		return $string;
	}
	
	/**
	*	Convert an integer into an English word string
	*	Thanks to Tom Nicholson <http://php.net/manual/en/function.strval.php#41988>
	*
	*	@input	an int
	*	@returns a string
	**/
	public static function numberToString($x)
	{
		$nwords = array( "zero", "one", "two", "three", "four", "five", "six", "seven",
			"eight", "nine", "ten", "eleven", "twelve", "thirteen",
			"fourteen", "fifteen", "sixteen", "seventeen", "eighteen",
			"nineteen", "twenty", 30 => "thirty", 40 => "forty",
			50 => "fifty", 60 => "sixty", 70 => "seventy", 80 => "eighty",
			90 => "ninety" );

		if(!is_numeric($x))
		{
			$w = $x;
		}
		elseif(fmod($x, 1) != 0)
		{
			$w = $x;
		}
		else
		{
			if($x < 0)
			{
				$w = 'minus ';
				$x = -$x;
			}
			else
			{
				$w = '';
				// ... now $x is a non-negative integer.
			}

			if($x < 21)   // 0 to 20
			{
				$w .= $nwords[$x];
			}
			elseif($x < 100)  // 21 to 99
			{ 
				$w .= $nwords[10 * floor($x/10)];
				$r = fmod($x, 10);
				if($r > 0)
				{
					$w .= ' '. $nwords[$r];
				}
			}
			elseif($x < 1000)  // 100 to 999
			{
				$w .= $nwords[floor($x/100)] .' hundred';
				$r = fmod($x, 100);
				if($r > 0)
				{
					$w .= ' and '. self::numberToString($r);
				}
			}
			elseif($x < 1000000)  // 1000 to 999999
			{
				$w .= self::numberToString(floor($x/1000)) .' thousand';
				$r = fmod($x, 1000);
				if($r > 0)
				{
					$w .= ' ';
					if($r < 100)
					{
						$w .= 'and ';
					}
					$w .= self::numberToString($r);
				}
			} 
			else //  millions
			{    
				$w .= self::numberToString(floor($x/1000000)) .' million';
				$r = fmod($x, 1000000);
				if($r > 0)
				{
					$w .= ' ';
					if($r < 100)
					{
						$w .= 'and ';
					}
					$w .= self::numberToString($r);
				}
			}
		}
		return $w;
	}

	/**
	*	Random Key
	*
	*	@returns a string
	**/
	public static function randomkey($size)
	{
		$bag = "abcefghijknopqrstuwxyzABCDDEFGHIJKLLMMNOPQRSTUVVWXYZabcddefghijkllmmnopqrstuvvwxyzABCEFGHIJKNOPQRSTUWXYZ";
		$key = array();
		$bagsize = strlen($bag) - 1;
		for ($i = 0; $i < $size; $i++)
		{
			$get = rand(0, $bagsize);
			$key[] = $bag[$get];
		}
		return implode($key);
	}
}
