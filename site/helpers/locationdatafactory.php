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

	phpIp2Country class (origin)
 
	@author Mariusz Górski
	@copyright 2008 Mariusz Górski
	@name phpIp2Country
	@version 1.0
	@link http://code.google.com/p/php-ip-2-country/

 
 	@version 	1.0.0  December 11, 2014
 	@package 	Locationdata API
 	@author  	Llewellyn van der Merwe <llewellyn@vdm.io>
 	@adapted	class phpIp2Country to LocationdataFactory class for Joomla 3
 	@copyright	Copyright (C) 2013 Vast Development Method <http://www.vdm.io>
 	@license	GNU General Public License <http://www.gnu.org/copyleft/gpl.html>


/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class LocationdataFactory {

	/**
	 * IP address
	 *
	 * @var string
	 */
	public $ip = '';

	/**
	 * IP protocol
	 *
	 * @var int
	 */
	public $protocol;

	/**
	 * Use Default Currency
	 *
	 * @var bool
	 */
	public $default = false;

	/**
	 * The Default Component
	 *
	 * @var string
	 */
	public $defaultComponent;
	
	/**
	 * Numerical representation of IP address
	 *       Example: (from Right to Left)
	 *       1.2.3.4 = 4 + (3 * 256) + (2 * 256 * 256) + (1 * 256 * 256 * 256)
	 *       is 4 + 768 + 13,1072 + 16,777,216 = 16,909,060
	 * @var integer
	 */
	private $ipValue = NULL;
	
	/**
	 * IP address in form of array of integer values
	 *
	 * @var string
	 */
	private $ipArr = array();
	
	/**
	 * IP address information array
	 *
	 * @var string
	 */
	private $ipInfoArr = false;
	
	/**
	 * @param string $ip
	 * @param ip $method
	 */
	function __construct($ip, $protocol = 4, $defaultComponent = 'com_locationdata')
	{
		// set the default component
		$this->defaultComponent = $defaultComponent;
		if (4 == $protocol && $this->checkIpAddr($ip))
		{
			// set IP
			$this->ip		= $ip;
			// set ip protocol
			$this->protocol		= $protocol;
			// get IP address
			$this->ipArr		= $this->getIpArr();
			// get IP value
			$this->ipValue		= $this->getIpValue();
			// get IP data
			$this->ipInfoArr	= $this->getIpdata();
			// check if IP info is set
			if (!$this->ipInfoArr)
			{
				return false;
			}
			// all is set and ready to go
			$this->ipInfoArr['IP_STR']	= $this->ip;
			$this->ipInfoArr['IP_VALUE']	= (string) $this->ipValue;
			$this->ipInfoArr['IP_FROM_STR'] = $this->getIpFromValue($this->ipInfoArr['IP_FROM']);
			$this->ipInfoArr['IP_TO_STR']	= $this->getIpFromValue($this->ipInfoArr['IP_TO']);
			return true;
		}
		// if IP error detected fall back on default
		if (!$ip || $ip == '0.0.0.0' || !$this->checkIpAddr($ip) || 1 == $protocol || 6 == $protocol) // TODO we must still add ipv6 implementation.
		{
			$this->default = true;
		}
		return false;
	}
	
	
	/**
	*
	*	returns information about IP adrress and currency exchanges
	*
	*	@param integer $mode
	*	@return mixed
	*
	**/
	public function getInfo($mode = 0, $string = 0, $value = 1.00, $base)
	{
		// load the exchange rate if needed
		$this->setExchangeRate($base, $value);
		// now return selected values
		if (!in_array($mode,range(1, 33)))
		{
			// return all
			return $this->ipInfoArr;
		}
		else
		{
			// the return values of mode
			$get = array(	1 => 'IP_STR',
					2 => 'IP_VALUE',
					3 => 'IP_RANGE_NUMERICAL',
					4 => 'IP_RANGE',
					5 => 'IP_REGISTRY',
					6 => 'IP_ASSIGNED_UNIXTIME',
					7 => 'COUNTRY_ALL',
					8 => 'COUNTRY_NAME',
					9 => 'COUNTRY_CODE_TWO',
					10 => 'COUNTRY_CODE_THREE',
					11 => 'CURRENCY_ALL',
					12 => 'CURRENCY_NAME',
					13 => 'CURRENCY_CODE_THREE',
					14 => 'CURRENCY_CODE_NUMERIC',
					15 => 'CURRENCY_SYMBOL',
					16 => 'CURRENCY_DECIMAL_PLACE',
					17 => 'CURRENCY_DECIMAL_SYMBOL',
					18 => 'CURRENCY_POSITIVE_STYLE',
					19 => 'CURRENCY_NEGATIVE_STYLE',
					20 => 'EXCHANGE_RATE_ALL',
					21 => 'EXCHANGE_RATE_ID',
					22 => 'EXCHANGE_RATE_NAME',
					23 => 'EXCHANGE_RATE',
					24 => 'EXCHANGE_RATE_ASK',
					25 => 'EXCHANGE_RATE_BID',
					26 => 'EXCHANGE_RATE_DATE', 
					27 => 'EXCHANGE_RATE_MONEY',
					28 => 'EXCHANGE_RATE_ASK_MONEY',
					29 => 'EXCHANGE_RATE_BID_MONEY',
					30 => 'EXCHANGE_RATE_FROM', 
					31 => 'EXCHANGE_RATE_FROM_MONEY',
					32 => 'EXCHANGE_RATE_TO',
					33 => 'EXCHANGE_RATE_TO_MONEY');	
			switch($mode)
			{
				case 3:
					if (!$this->default)
					{
						return array(
							'FROM'	=> $this->ipInfoArr['IP_FROM'],
							'TO'	=> $this->ipInfoArr['IP_TO']
						);
					}
					break;
				case 4:
					if (!$this->default)
					{
						return array(
							'FROM'	=> $this->ipInfoArr['IP_FROM_STR'],
							'TO'	=> $this->ipInfoArr['IP_TO_STR']
						);
					}
					break;
				case 7:
					if (!$this->default)
					{
						return array(
							'COUNTRY_NAME'		=> $this->ipInfoArr['COUNTRY_NAME'],
							'COUNTRY_CODE_TWO'	=> $this->ipInfoArr['COUNTRY_CODE_TWO'],
							'COUNTRY_CODE_THREE'	=> $this->ipInfoArr['COUNTRY_CODE_THREE']
						);
					}
					break;
				case 11:
					if (strlen($this->ipInfoArr['CURRENCY_NAME']) > 0)
					{
						return array(
							'CURRENCY_NAME'			=> $this->ipInfoArr['CURRENCY_NAME'],
							'CURRENCY_CODE_NUMERIC'		=> $this->ipInfoArr['CURRENCY_CODE_NUMERIC'],
							'CURRENCY_CODE_THREE'		=> $this->ipInfoArr['CURRENCY_CODE_THREE'],
							'CURRENCY_SYMBOL'		=> $this->ipInfoArr['CURRENCY_SYMBOL'],
							'CURRENCY_DECIMAL_PLACE'	=> $this->ipInfoArr['CURRENCY_DECIMAL_PLACE'],
							'CURRENCY_DECIMAL_SYMBOL'	=> $this->ipInfoArr['CURRENCY_DECIMAL_SYMBOL'],
							'CURRENCY_POSITIVE_STYLE'	=> $this->ipInfoArr['CURRENCY_POSITIVE_STYLE'],
							'CURRENCY_NEGATIVE_STYLE'	=> $this->ipInfoArr['CURRENCY_NEGATIVE_STYLE']
						);
					}
					return false;
					break;
				case 20:
					if (strlen($this->ipInfoArr['EXCHANGE_RATE_ID']) > 0)
					{
						return array(
							'EXCHANGE_RATE_ID'		=> $this->ipInfoArr['EXCHANGE_RATE_ID'],
							'EXCHANGE_RATE_NAME'		=> $this->ipInfoArr['EXCHANGE_RATE_NAME'],
							'EXCHANGE_RATE'			=> $this->ipInfoArr['EXCHANGE_RATE'],
							'EXCHANGE_RATE_ASK'		=> $this->ipInfoArr['EXCHANGE_RATE_ASK'],
							'EXCHANGE_RATE_BID'		=> $this->ipInfoArr['EXCHANGE_RATE_BID'],
							'EXCHANGE_RATE_MONEY'		=> $this->ipInfoArr['EXCHANGE_RATE_MONEY'],
							'EXCHANGE_RATE_ASK_MONEY'	=> $this->ipInfoArr['EXCHANGE_RATE_ASK_MONEY'],
							'EXCHANGE_RATE_BID_MONEY'	=> $this->ipInfoArr['EXCHANGE_RATE_BID_MONEY'],
							'EXCHANGE_RATE_FROM'		=> $this->ipInfoArr['EXCHANGE_RATE_FROM'],
							'EXCHANGE_RATE_FROM_MONEY'	=> $this->ipInfoArr['EXCHANGE_RATE_FROM_MONEY'],
							'EXCHANGE_RATE_TO'		=> $this->ipInfoArr['EXCHANGE_RATE_TO'],
							'EXCHANGE_RATE_TO_MONEY'	=> $this->ipInfoArr['EXCHANGE_RATE_TO_MONEY'],
							'EXCHANGE_RATE_DATE'		=> $this->ipInfoArr['EXCHANGE_RATE_DATE']
						);
					}
					return false;
					break;
				default:
					if ($string)
					{
						return $this->ipInfoArr[$get[$mode]];						
					}
					else
					{
						return array( $get[$mode] => $this->ipInfoArr[$get[$mode]] );
					}
					break;
			}
		}
		return false;
	}
	
	/**
	 * validate IP address
	 *
	 * @param string $ip
	 * @return boolean
	 */
	private function checkIpAddr($ip='')
	{
		return preg_match('/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/i',$ip);
	}
	
	/**
	 * returns IP address in array of integer values
	 *
	 * @return array
	 */
	private function getIpArr()
	{
		$vars = explode('.',$this->ip);
		return array(
			intval($vars[0]),
			intval($vars[1]),
			intval($vars[2]),
			intval($vars[3])
		);
	}
	
	/**
	 * returns numerical representation of IP address.
	 *       Example: (from Right to Left)
	 *       1.2.3.4 = 4 + (3 * 256) + (2 * 256 * 256) + (1 * 256 * 256 * 256)
	 *       is 4 + 768 + 13,1072 + 16,777,216 = 16,909,060
	 *
	 * @return integer
	 */
	private function getIpValue()
	{
		return $this->ipArr[3] + ( $this->ipArr[2] * 256 ) + ( $this->ipArr[1] * 256 * 256 ) + ( $this->ipArr[0] * 256 * 256 * 256 );
	}
	
	/**
	 * returns IP numer from numerical representation.
	 *       Example: (from Right to Left)
	 *       1.2.3.4 = 4 + (3 * 256) + (2 * 256 * 256) + (1 * 256 * 256 * 256)
	 *       is 4 + 768 + 13,1072 + 16,777,216 = 16,909,060
	 *
	 * @param integer $value
	 * @param boolean $returnAsStr
	 * @return mixed
	 */
	private function getIpFromValue($value = 0, $returnAsStr = true)
	{
		$ip[0] = floor( intval($value) / (256*256*256) );
		$ip[1] = floor( ( intval($value) - $ip[0]*256*256*256 ) / (256*256) );
		$ip[2] = floor( ( intval($value) -$ip[0]*256*256*256 -$ip[1]*256*256 ) / 256 );
		$ip[3] = intval($value) - $ip[0]*256*256*256 - $ip[1]*256*256 - $ip[2]*256;
		if($returnAsStr)
		{
			return $ip[0].'.'.$ip[1].'.'.$ip[2].'.'.$ip[3];
		}
		else
		{
			return $ip;
		}
	}
	
	/**
	 * returns IP Data from #__locationdata_ip_table database
	 *
	 * @return string
	 */
	private function getIpdata()
	{		
		$get = array(	'IP_FROM'			=> 'a.ip_from', 
				'IP_TO'				=> 'a.ip_to', 
				'IP_REGISTRY'			=> 'a.registry',
				'IP_ASSIGNED'			=> 'a.assigned',
				'COUNTRY_NAME'			=> 'b.name',
				'COUNTRY_CODE_TWO'		=> 'b.codetwo',
				'COUNTRY_CODE_THREE'		=> 'a.cntry',
				'CURRENCY_NAME'			=> 'c.name',
				'CURRENCY_CODE_THREE'		=> 'c.codethree',
				'CURRENCY_CODE_NUMERIC'		=> 'c.numericcode',
				'CURRENCY_SYMBOL'		=> 'c.symbol',
				'CURRENCY_DECIMAL_PLACE'	=> 'c.decimalplace',
				'CURRENCY_DECIMAL_SYMBOL'	=> 'c.decimalsymbol',
				'CURRENCY_POSITIVE_STYLE'	=> 'c.positivestyle',
				'CURRENCY_NEGATIVE_STYLE'	=> 'c.negativestyle');
		// Get a db connection.
		$db = JFactory::getDbo();
		// Create a new query object.
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array_values($get),array_keys($get)))
			->from($db->quoteName('#__locationdata_ip_table', 'a'))
			->join('INNER', $db->quoteName('#__locationdata_country', 'b') . ' ON (' . $db->quoteName('a.cntry') . ' = ' . $db->quoteName('b.codethree') . ')')
			->join('INNER', $db->quoteName('#__locationdata_currency', 'c') . ' ON (' . $db->quoteName('b.currency') . ' = ' . $db->quoteName('c.codethree') . ')')
			->where($db->quoteName('a.ip_from') . ' <= ' . (int) $this->ipValue)
			->where($db->quoteName('a.ip_to') . ' >= ' . (int) $this->ipValue)
			->where($db->quoteName('a.protocol') . ' = ' . (int) $this->protocol);
		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		return $db->loadAssoc();
	}
	
	/**
	 * returns ExchangeRate from #__locationdata_exchange_rate database
	 *
	 * @return string
	 */
	private function setExchangeRate($from, $fromValue = null, $retry = false)
	{
		// should we use default
		if ($this->default || $retry)
		{
			$this->ipInfoArr = $this->getCurrencyDetails();
		}
		// only continue if is from is set
		if($from)
		{
			if ($from == $this->ipInfoArr['CURRENCY_CODE_THREE'])
			{
				// it is the same currency so set all one on one
				$this->ipInfoArr['EXCHANGE_RATE_ID']		= strtolower($from).'-'.strtolower($from);
				$this->ipInfoArr['EXCHANGE_RATE_NAME']		= $from.$from;
				$this->ipInfoArr['EXCHANGE_RATE']		= 1.00;
				$this->ipInfoArr['EXCHANGE_RATE_ASK']		= 1.00;
				$this->ipInfoArr['EXCHANGE_RATE_BID']		= 1.00;
				$this->ipInfoArr['EXCHANGE_RATE_DATE']		= 0;
				// now set the money
				$this->ipInfoArr['EXCHANGE_RATE_MONEY']		= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE'], 'ip');
				$this->ipInfoArr['EXCHANGE_RATE_ASK_MONEY']	= $this->ipInfoArr['EXCHANGE_RATE_MONEY'];
				$this->ipInfoArr['EXCHANGE_RATE_BID_MONEY']	= $this->ipInfoArr['EXCHANGE_RATE_MONEY'];
				if (!$fromValue)
				{
					$fromValue = 1.00;
				}
				// set the convertion
				$this->ipInfoArr['EXCHANGE_RATE_FROM']		= (float) $fromValue;
				$this->ipInfoArr['EXCHANGE_RATE_FROM_MONEY']	= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE_FROM'], $from);
				// calculate the new value
				$this->ipInfoArr['EXCHANGE_RATE_TO']		= (float) $fromValue;
				$this->ipInfoArr['EXCHANGE_RATE_TO_MONEY']	= $this->ipInfoArr['EXCHANGE_RATE_FROM_MONEY'];
				return true;				
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
				->where($db->quoteName('a.to') . ' = '. $db->quote($this->ipInfoArr['CURRENCY_CODE_THREE']))
				->where($db->quoteName('a.published') . ' = 1');
			// Reset the query using our newly populated query object.
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$rates = $db->loadAssoc();
				// load in to active data
				foreach ($rates as $key => $value)
				{
					$this->ipInfoArr[$key] = $value;
				}
				// now set the money
				$this->ipInfoArr['EXCHANGE_RATE_MONEY']		= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE'], 'ip');
				$this->ipInfoArr['EXCHANGE_RATE_ASK_MONEY']	= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE_ASK'], 'ip');
				$this->ipInfoArr['EXCHANGE_RATE_BID_MONEY']	= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE_BID'], 'ip');
				if (!$fromValue)
				{
					$fromValue = 1.00;
				}
				// set the convertion
				$this->ipInfoArr['EXCHANGE_RATE_FROM']		= (float) $fromValue;
				$this->ipInfoArr['EXCHANGE_RATE_FROM_MONEY']	= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE_FROM'], $from);
				// calculate the new value
				$this->ipInfoArr['EXCHANGE_RATE_TO']		= (float) bcmul($this->ipInfoArr['EXCHANGE_RATE_FROM'], $this->ipInfoArr['EXCHANGE_RATE'], 4);
				$this->ipInfoArr['EXCHANGE_RATE_TO_MONEY']	= $this->makeMoney($this->ipInfoArr['EXCHANGE_RATE_TO'], 'ip');
				return true;
			}
			if (!$retry)
			{
				// retrun once more with default if first try did not work
				return $this->setExchangeRate($from, $fromValue, true);
			}
		}
		// the resetting values
		$resetting = array(
			'EXCHANGE_RATE_ID',
			'EXCHANGE_RATE_NAME', 
			'EXCHANGE_RATE',
			'EXCHANGE_RATE_ASK',
			'EXCHANGE_RATE_BID', 
			'EXCHANGE_RATE_MONEY',
			'EXCHANGE_RATE_ASK_MONEY',
			'EXCHANGE_RATE_BID_MONEY',
			'EXCHANGE_RATE_FROM', 
			'EXCHANGE_RATE_FROM_MONEY',
			'EXCHANGE_RATE_TO',
			'EXCHANGE_RATE_TO_MONEY',
			'EXCHANGE_RATE_DATE');
		foreach ($resetting as $reset)
		{
			$this->ipInfoArr[$reset] = null;
		}
		return false;
	}
	
	/**
	 * returns value in a currency
	 *
	 * @return string
	 */
	private function makeMoney($number, $currency = 'ip')
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
		// load currency details
		if ('ip' == $currency)
		{
			// use global IP data
			$currency = $this->ipInfoArr;
		}
		else
		{
			// load the currency details in
			$currency = $this->getCurrencyDetails($currency);
		}
		// set the number to currency
		if (is_array($currency) && count($currency) > 4)
		{
			if (!$negative)
			{
				$format = $currency['CURRENCY_POSITIVE_STYLE'];
				$sign = '+';
			}
			else 
			{
				$format = $currency['CURRENCY_NEGATIVE_STYLE'];
				$sign = '-';
				$number = abs($number);
			}
			$setupNumber = number_format((float)$number, (int)$currency['CURRENCY_DECIMAL_PLACE'], $currency['CURRENCY_DECIMAL_SYMBOL'], ' '); //$currency->currency_thousands TODO);
			$search = array('{sign}', '{number}', '{symbol}');
			$replace = array($sign, $setupNumber, $currency['CURRENCY_SYMBOL']);
			$moneyMade = str_replace ($search,$replace,$format);

			return $moneyMade;
		}
		return $number;
	}

	protected $currencyDetails = array();

	protected function getCurrencyDetails($codethree = false)
	{
		// check if currency codethree is set
		if (!$codethree)
		{
			// get the main currency
			$codethree = JComponentHelper::getParams($this->defaultComponent)->get('currency', 'USD');
		}
		// return cached data if set
		if ($codethree && !isset($this->currencyDetails[$codethree]))
		{
			// Get a db connection.
			$db = JFactory::getDbo();
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(
				array( 'a.name','a.codethree','a.numericcode','a.symbol','a.thousands','a.decimalplace',
					'a.decimalsymbol','a.positivestyle','a.negativestyle'),
				array(	'CURRENCY_NAME','CURRENCY_CODE_THREE','CURRENCY_CODE_NUMERIC','CURRENCY_SYMBOL','CURRENCY_THOUSANDS','CURRENCY_DECIMAL_PLACE',
					'CURRENCY_DECIMAL_SYMBOL','CURRENCY_POSITIVE_STYLE','CURRENCY_NEGATIVE_STYLE')));
			$query->from($db->quoteName('#__locationdata_currency', 'a'));
			if (strlen($codethree) == 3)
			{
				$query->where($db->quoteName('a.codethree') . ' = '.$db->quote($codethree));
			}
			elseif (is_numeric($codethree))
			{
				$query->where($db->quoteName('a.id') . ' = '. (int) $codethree);
			}
			else
			{
				$query->where($db->quoteName('a.codethree') . ' = '.$db->quote('NONE'));
			}
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$this->currencyDetails[$codethree] = $db->loadAssoc();
			}
		}
		// make sure it has been set
		if (isset($this->currencyDetails[$codethree]))
		{
			return $this->currencyDetails[$codethree];
		}
		return false;
	}
}

