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

	@version		@update number 101 of this MVC
	@build			18th January, 2017
	@created		28th June, 2016
	@package		Location Data
	@subpackage		updatedata.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * Locationdata Updatedata Model
 */
class LocationdataModelUpdatedata extends JModelItem
{
	/**
	 * Model context string.
	 *
	 * @var        string
	 */
	protected $_context = 'com_locationdata.updatedata';

        /**
	 * Model user data.
	 *
	 * @var        strings
	 */
        protected $user;
        protected $userId;
        protected $guest;
        protected $groups;
        protected $levels;
	protected $app;
	protected $input;
	protected $uikitComp;

	/**
	 * @var object item
	 */
	protected $item;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 *
	 * @return void
	 */
	protected function populateState()
	{
		$this->app	= JFactory::getApplication();
		$this->input 	= $this->app->input;
		// Get the item main id
		$id		= $this->input->getInt('id', null);
		$this->setState('updatedata.id', $id);

		// Load the parameters.
		parent::populateState();
	}

	/**
	 * Method to get article data.
	 *
	 * @param   integer  $pk  The id of the article.
	 *
	 * @return  mixed  Menu item data object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$this->user	= JFactory::getUser();
                // check if this user has permission to access item
                if (!$this->user->authorise('updatedata.access', 'com_locationdata'))
                {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('Not authorised!'), 'error');
			// redirect away if not a correct to cPanel/default view
			$app->redirect('index.php?option=com_locationdata');
			return false;
                }
		$this->userId		= $this->user->get('id');
		$this->guest		= $this->user->get('guest');
                $this->groups		= $this->user->get('groups');
                $this->authorisedGroups	= $this->user->getAuthorisedGroups();
		$this->levels		= $this->user->getAuthorisedViewLevels();
		$this->initSet		= true;

		$pk = (!empty($pk)) ? $pk : (int) $this->getState('updatedata.id');

		$this->mainCurrency = JComponentHelper::getParams('com_locationdata')->get('base_currency', 'USD');
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				// Get a db connection.
				$db = JFactory::getDbo();

				// Create a new query object.
				$query = $db->getQuery(true);

				// Get from #__locationdata_country as a
				$query->select($db->quoteName(
			array('a.currency','a.codethree'),
			array('currency','codethree')));
				$query->from($db->quoteName('#__locationdata_country', 'a'));
				// Check if $this->mainCurrency is a string or numeric value.
				$checkValue = $this->mainCurrency;
				if (isset($checkValue) && LocationdataHelper::checkString($checkValue))
				{
					$query->where('a.currency = ' . $db->quote($checkValue));
				}
				elseif (is_numeric($checkValue))
				{
					$query->where('a.currency = ' . $checkValue);
				}
				else
				{
					return false;
				}

				// Reset the query using our newly populated query object.
				$db->setQuery($query);
				// Load the results as a stdClass object.
				$data = $db->loadObject();

				if (empty($data))
				{
					$app = JFactory::getApplication();
					// If no data is found redirect to default page and show warning.
					$app->enqueueMessage(JText::_('COM_LOCATIONDATA_NOT_FOUND_OR_ACCESS_DENIED'), 'warning');
					$app->redirect('index.php?option=com_locationdata');
					return false;
				}
				// set the global mainCountry value.
				$this->a_mainCountry = $data->codethree;

				// set data object to item.
				$this->_item[$pk] = $data;
                        }
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseWaring(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}


	/**
	* Custom Method
	*
	* @return mixed  item data object on success, false on failure.
	*
	*/
	public function getLastExhangeUpdate()
	{

		if (!isset($this->initSet) || !$this->initSet)
		{
			$this->user		= JFactory::getUser();
			$this->userId		= $this->user->get('id');
			$this->guest		= $this->user->get('guest');
			$this->groups		= $this->user->get('groups');
			$this->authorisedGroups	= $this->user->getAuthorisedGroups();
			$this->levels		= $this->user->getAuthorisedViewLevels();
			$this->initSet		= true;
		}
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__locationdata_exchange_rate as a
		$query->select($db->quoteName(
			array('a.from','a.created'),
			array('from','created')));
		$query->from($db->quoteName('#__locationdata_exchange_rate', 'a'));
		// Check if $this->mainCurrency is a string or numeric value.
		$checkValue = $this->mainCurrency;
		if (isset($checkValue) && LocationdataHelper::checkString($checkValue))
		{
			$query->where('a.from = ' . $db->quote($checkValue));
		}
		elseif (is_numeric($checkValue))
		{
			$query->where('a.from = ' . $checkValue);
		}
		else
		{
			return false;
		}

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		// Load the results as a stdClass object.
		$data = $db->loadObject();

		if (empty($data))
		{
			return false;
		}

		// return data object.
		return $data;
	}


	/**
	* Custom Method
	*
	* @return mixed  item data object on success, false on failure.
	*
	*/
	public function getLastIpUpdate()
	{

		if (!isset($this->initSet) || !$this->initSet)
		{
			$this->user		= JFactory::getUser();
			$this->userId		= $this->user->get('id');
			$this->guest		= $this->user->get('guest');
			$this->groups		= $this->user->get('groups');
			$this->authorisedGroups	= $this->user->getAuthorisedGroups();
			$this->levels		= $this->user->getAuthorisedViewLevels();
			$this->initSet		= true;
		}
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__locationdata_ip_table as a
		$query->select($db->quoteName(
			array('a.cntry','a.created'),
			array('cntry','created')));
		$query->from($db->quoteName('#__locationdata_ip_table', 'a'));
		// Check if $this->a_mainCountry is a string or numeric value.
		$checkValue = $this->a_mainCountry;
		if (isset($checkValue) && LocationdataHelper::checkString($checkValue))
		{
			$query->where('a.cntry = ' . $db->quote($checkValue));
		}
		elseif (is_numeric($checkValue))
		{
			$query->where('a.cntry = ' . $checkValue);
		}
		else
		{
			return false;
		}

		// Reset the query using our newly populated query object.
		$db->setQuery($query);
		// Load the results as a stdClass object.
		$data = $db->loadObject();

		if (empty($data))
		{
			return false;
		}

		// return data object.
		return $data;
	}


	/**
	* Get the uikit needed components
	*
	* @return mixed  An array of objects on success.
	*
	*/
	public function getUikitComp()
	{
		if (isset($this->uikitComp) && LocationdataHelper::checkArray($this->uikitComp))
		{
			return $this->uikitComp;
		}
		return false;
	}  

	protected $lastIPUpdate = null;
	protected $lastRateUpdate = null;

	public function getUpdateStats()
	{
		$stats = new stdClass;
		$stats->lastIPUpdate = $this->lastIPUpdate;
		$stats->lastRateUpdate = $this->lastRateUpdate;
		// hand the stats to the view
		return $stats;
	}

	/**
	* 	the queue status array
	**/
	protected $queue = array();

	/**
	* 	updating the queue status check/force
	**/
	public function runQueue(&$time, &$force, $updateThis)
	{
                // get params
		$this->app_params = JComponentHelper::getParams('com_locationdata');
                // rest return array
		$return = array();
		// Set what should be updated
		switch($updateThis)
		{
			case 'exchange_rate':
				$types = array('exchange_rate');
			break;
			case 'ip_table':
				$types = array('ip_table');
			break;
			default:
				$types = array('exchange_rate', 'ip_table');
			break;
		}
		// check if we should force and update
		if (2 == $force)
		{
			foreach ($types as $type)
			{
				$return[$type] = $this->startQueue($type, $time);
			}
		}
		else
		{
			foreach ($types as $type)
			{
				$return[$type] = $this->getQueue($type, $time);
			}
		}
		return $return;
	}
	
	protected function getQueue(&$type, &$time)
	{
		if (!isset($this->queue[$type]))
		{
			// if there is no queue for this type, then start one
			if ($this->setQueue($type, $time))
			{
				return $this->startQueue($type, $time);
			}
		}
		return $this->queue[$type];
	}
	
	protected function setQueue(&$type, &$time)
	{
		$filePath = self::getFilePath($type,'queue');
		// get the set queue
		if (($queue = @file_get_contents($filePath)) !== FALSE)
		{			
			if (LocationdataHelper::checkJson($queue))
			{
				// get set queue
				$queue = json_decode($queue);
				if (LocationdataHelper::checkObject($queue) && $queue->active)
				{
					// in progress
					$this->queue[$type] = false;
				}
				elseif (LocationdataHelper::checkObject($queue))
				{
					// get interval of update and check if next update is due
					$this->queue[$type] = $this->checkQueue($type, $queue->date);
				}
			}
		}
		if (!isset($this->queue[$type]))
		{
			// get interval of update and check if next update is due
			$last = 1234;
			$this->queue[$type] = $this->checkQueue($type, $last);
		}
		return $this->queue[$type];
	}

	protected function checkQueue(&$type,  &$last)
	{
		// get timer
		$timer = $this->app_params->get('timer_'.$type, '-12 hours');
		$next = JFactory::getDate()->modify($timer)->toUnix();
		// check if it is time for the next update
		if ((int) $next > (int) $last)
		{
			return true;
		}
		return false;
	}

	protected function startQueue(&$type, &$time)
	{
		// start a path
		$filePath = self::getFilePath($type,'queue');
		// set queue to active
		$set = '{"active": true,"date": '.(int) $time.'}';
		// set that a update is running
		if ($this->saveJson($set, $filePath))
		{
			$done = $this->{'update_'.$type}();
			$set = '{"active": false,"date": '.(int) $time.'}';
			// set that a update is completed
			if ($this->saveJson($set, $filePath))
			{
				return $done;
			}
		}
		return false;
	}
		
	 /**
	 * upload the latest file.
	 *
	 * @return  a bool
	 *
	 */
	protected function upload($url,$filename,$csv, &$config, &$tmp_dest)
	{
		// Did you give us a URL?
		if (!$url)
		{
			return false;
		}
		if (file_exists($tmp_dest.'/'.$csv))
		{
			// already uploaded
			return true;
		}
		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url,$filename);
		// Was the package downloaded?
		if (!$p_file)
		{
			return false;
		}
		if (file_exists($tmp_dest.'/'.$p_file))
		{
			$ext = pathinfo($tmp_dest.'/'.$p_file, PATHINFO_EXTENSION);
			if ('zip' == $ext)
			{
				// Unpack the downloaded package file
				$zip = new ZipArchive;
				$res = $zip->open($tmp_dest.'/'.$p_file);
				if ($res === TRUE)
				{
					$zip->extractTo($tmp_dest);
					$zip->close();
					// remove zip file
					return JFile::delete($tmp_dest.'/'.$p_file);
				}
			}
			elseif ('gz' == $ext)
			{
				$this->gzUncompress($tmp_dest.'/'.$p_file, $tmp_dest.'/'.$csv);
				// remove zip file
				return JFile::delete($tmp_dest.'/'.$p_file);
			}
		}
		// just in case.... 
		JFile::delete($tmp_dest.'/'.$p_file);
		return false;
	}

	protected function gzUncompress($srcName, $dstName)
	{
		$sfp = gzopen($srcName, "rb");
		$fp = fopen($dstName, "w");

		while (!gzeof($sfp)) {
		    $string = gzread($sfp, 4096);
		    fwrite($fp, $string, strlen($string));
		}
		gzclose($sfp);
		fclose($fp);
	}

	/**
	 * All the exchange rates from GITHUB
	 *
	 * @var array
	 */
	protected $exchangerates;

	/**
	 * Currencies that are active
	 *
	 * @var string
	 */
	protected $currencies;

	/**
	 * The url to ALL GITHUB Exchange Rates
	 *
	 * @var string
	 */
	protected $allRatesUrl = 'https://raw.githubusercontent.com/ExchangeRates/yahoo/master/rates.json';

	protected function update_exchange_rate()
	{
		// check if there is another url given to get rates from
		if ($all_rates_url = $this->app_params->get('all_rates_url', null))
		{
			$this->allRatesUrl = $all_rates_url;
		}
		// Get a db connection.
		$this->db = JFactory::getDbo();
		// Get a currencies
		$this->currencies = $this->getCurrencies();
		// set the data
		if(LocationdataHelper::checkArray($this->currencies) && empty($this->exchangerate))
		{
			// check if we can use curl
			if (function_exists('curl_version'))
			{
				$ch = curl_init();
				$timeout = 0;
				curl_setopt($ch, CURLOPT_URL, $this->allRatesUrl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)');
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$this->exchangerates = json_decode(curl_exec($ch), true);
				curl_close($ch);
			}
			elseif (($rate = @file_get_contents($this->allRatesUrl)) !== FALSE)
			{
				$this->exchangerates = json_decode($rate, true);
			}
		}
		// insure we have the data set
		if (LocationdataHelper::checkArray($this->exchangerates))
		{
			$mainCurrency = $this->app_params->get('currency', 'USD');
			if (!in_array($mainCurrency, $this->currencies))
			{
				$this->currencies[] = $mainCurrency;
			}
			// delete the old values
			$this->deleteOldRates();
			// now store the new rates
			return $this->storeExchangeRates();
		}
		return false;
	}
	
	protected function storeExchangeRates()
	{
		// get creation date
		$this->date_created = JFactory::getDate()->toSql();
		// current user
		$this->userId = JFactory::getUser()->id;
		// Insert columns.
		$this->columns = array('name', 'alias', 'from', 'to', 'rate', 'date_rate', 'ask', 'bid', 'published', 'created', 'created_by','version','access');
		// set buket value arrays
		$buket			= array();
		$this->values		= array();
		$this->queryCounter	= 0;
		foreach($this->currencies as $currency)
		{
			$buket[] = $currency;
			if(count($buket) == 3)
			{
				$this->setRateValues($buket);
			}
		}
		// check if last set was processed
		if(LocationdataHelper::checkArray($buket))
		{
			$this->setRateValues($buket);
		}
		// if there is still values then load them
		if(LocationdataHelper::checkArray($this->values))
		{
			$this->storeRates();
		}
		return true;
	}

	protected function setRateValues(&$buket)
	{
		foreach($buket as $base)
		{
			foreach($this->currencies as $currency)
			{
				if ($base != $currency && isset($this->exchangerates[$base.$currency]))
				{
						$this->buildRates($this->exchangerates[$base.$currency]);
				}
			}
		}
		// always insure that the bucket is emptied
		$buket = array();
	}

	protected function buildRates(&$rate)
	{
		if (isset($rate['id']) && isset($rate['Name']))
		{
			// to get this date format 0000-00-00 00:00:00
			$date_rate	= date("Y-m-d", strtotime($rate['Date']));
			$date_rate	.= ' '.date("H:i:s", strtotime($rate['Time']));
			// get the from and to value
			list($from,$to) = explode('/',$rate['Name']);
			$alias = strtolower(str_replace('/', '-', $rate['Name']));
			// now set the values
			$this->values[] = array(
				$this->db->quote($rate['id']),
				$this->db->quote($alias),
				$this->db->quote(trim($from)),
				$this->db->quote(trim($to)),
				$this->db->quote($rate['Rate']),
				$this->db->quote($date_rate),
				$this->db->quote($rate['Ask']),
				$this->db->quote($rate['Bid']),
				1,
				$this->db->quote($this->date_created),
				(int) $this->userId,
				1,
				1);
			$this->oldValues[] = $rate['id'];
		}
		// once there is more then 400 records store them
		if(count($this->values) > 400)
		{
			$this->storeRates();
		}
	}
	
	protected function storeRates()
	{
		// Create a new query object.
		$query = $this->db->getQuery(true);
		// Prepare the insert query.
		$query->insert($this->db->quoteName('#__locationdata_exchange_rate'));
		$query->columns($this->db->quoteName($this->columns));
		foreach($this->values as $value)
		{
			$query->values(implode(',', $value));
		}
		// clear the values array
		$this->values = array();
		// Set the query using our newly populated query object and execute it.
		$this->db->setQuery($query);
		$this->db->execute();
	}
	
	 /**
	 * Turn active update off if older then set time or remove.
	 *
	 * @return  a bool
	 *
	 */
	protected function deleteOldRates()
	{
		// clear table from old data
		$this->db->setQuery("TRUNCATE TABLE `#__locationdata_exchange_rate`");
		$this->db->execute();
	}
	
	 /**
	 * get all currencies.
	 *
	 * @return  a array
	 *
	 */
	protected function getCurrencies()
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('codethree'));
		$query->from('#__locationdata_currency');
		$query->where($this->db->quoteName('published')." = 1");
		$query->order('ordering ASC');
		$this->db->setQuery($query);
		return $this->db->loadColumn();
	}

	protected function update_ip_table()
	{
		$updater = array(	4 => array('url' => 'http://software77.net/geo-ip/?DL=2', 'pkg' => 'IpToCountry.csv.zip', 'file' => 'IpToCountry.csv'));
						// 6 => array('url' => 'http://software77.net/geo-ip/?DL=7', 'pkg' => 'IpToCountry.6R.csv.gz', 'file' => 'IpToCountry.6R.csv') 
						// TODO we still need to implement ipv6 so to add the data to the DB is not needed at this time
		// Import dependencies
		jimport('joomla.filesystem.file');
		// Get a db connection.
		$db = JFactory::getDbo();
		// get config
		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');
		// set some defaults
		$userId = JFactory::getUser()->id;
		$today = JFactory::getDate()->toSql();
		// upload ip Data
		foreach ($updater as $version => $wget)
		{
			if (!$this->upload($wget['url'], $wget['pkg'], $wget['file'], $config, $tmp_dest))
			{
				return false;
			}
			if(!file_exists($tmp_dest.'/'.$wget['file']))
			{
				return false;
			}
			// do update
			if(!$this->upDateIpData($tmp_dest.'/'.$wget['file'],  $version, $userId, $today, $db))
			{
				return false;
			}
		}
		return true;
	}

	/**
	* Update the DB with new data
	*
	*
	* @retunr a bool
	*
	**/
	protected function upDateIpData($filename, $ipVersion, &$userId, &$today, &$db)
	{		
		// set the data
		if(($handle = fopen($filename, 'r')) !== false )
		{
			// clear table from old data only once per update
			if (4 == $ipVersion)
			{
				$db->setQuery("TRUNCATE TABLE `#__locationdata_ip_table`");
				$db->execute();
			}
			$counter = 0;
			$values = array();
			// loop through the file line-by-line
			while(($data = fgetcsv($handle)) !== false)
			{	
				// remove comments and ...
				$check = implode(",", $data);	
				if (substr($check, 0, 1) == '#') continue;
				// now we have the correct data one more check
				if (4 == $ipVersion)
				{
					if (count($data) < 7) continue;
					// Insert values.
					$values[] = array(
						$db->quote($data[0]), $db->quote($data[1]), 
						$db->quote($data[2]), $db->quote($data[3]), 
						$db->quote($data[4]), $db->quote($data[5]), 
						4, 1, $db->quote($today), $userId, 1, 1);
				}
				// do some prep for version 6
				if (6 == $ipVersion)
				{
					if (count($data) < 4) continue;
					// set start and end value
					if (strpos($data[0], '-') !== false && ($codethree = LocationdataHelper::getVar('country', $data[1], 'codetwo', 'codethree')) && $data[3] != 0)
					{
						$array = array_map('trim', (array) explode('-', $data[0]));
						// Insert values.
						$values[] = array(
							$db->quote($array[0]), $db->quote($array[1]), 
							$db->quote($data[2]), $db->quote($data[3]), 
							 $db->quote($data[1]), $db->quote($codethree), 
							6, 1, $db->quote($today), $userId, 1, 1);
					}
					else
					{
						continue;
					}
				}
				// set counter
				$counter++;
				// set to db
				if($counter == 400){
					// Create a new query object.
					$query = $db->getQuery(true);
					 
					// Insert columns.
					$columns = array('ip_from', 'ip_to', 'registry', 'assigned', 'ctry', 'cntry','protocol','published','created','created_by','version','access');
					 
					// Prepare the insert query.
					$query->insert($db->quoteName('#__locationdata_ip_table'));
					$query->columns($db->quoteName($columns));
					foreach($values as $value){
						$query->values(implode(',', $value));
					}
					// clear the values array
					unset($values);
					$values = array();
					// Set the query using our newly populated query object and execute it.
					$db->setQuery($query);
					$db->execute();
					// rest counter
					$counter = 0;
				}
				// clear values
				unset($data);
			}
			fclose($handle);
			// load the last values that are less than 400
			if($counter > 0){
				// Create a new query object.
				$query = $db->getQuery(true);
				 
				// Insert columns.
				$columns = array('ip_from', 'ip_to', 'registry', 'assigned', 'ctry', 'cntry','protocol','published','created','created_by','version','access');
				 
				// Prepare the insert query.
				$query->insert($db->quoteName('#__locationdata_ip_table'));
				$query->columns($db->quoteName($columns));
				foreach($values as $value){
					$query->values(implode(',', $value));
				}
				// clear the values array
				unset($values);
				// Set the query using our newly populated query object and execute it.
				$db->setQuery($query);
				$db->execute();
				// rest counter
				$counter = 0;
			}
			// clear the uploaded files
			JFile::delete($filename);
			return true;
		}
		return false;
	}

	protected function getFilePath(&$type, $name)
	{
		if (!isset($this->{$type.$name}) || !LocationdataHelper::checkString($this->{$type.$name}))
		{
			// Get local key
			$localkey = $this->getLocalKey();
			// set the name
			$fileName = md5($type.$name.$localkey);
			// set file path			
			$this->{$type.$name} = JPATH_COMPONENT_ADMINISTRATOR.'/helpers/'.$fileName.'.json';
		}
		// return the path
		return $this->{$type.$name};
	}

	protected function saveJson($data,$filename)
	{
		if (LocationdataHelper::checkJson($data))
		{
			$fp = fopen($filename, 'w');
			fwrite($fp, $data);
			fclose($fp);
			return true;
		}
		return false;
	}

	/**
	* 	get the localkey
	**/
	protected $localkey = false;
	
	protected function getLocalKey()
	{
		if (!$this->localkey)
		{
			// get the main key
			$this->localkey = md5($this->app_params->get('local_encryption', 'localKey34fdsEkl'));
		}
		return $this->localkey;
	}
}
