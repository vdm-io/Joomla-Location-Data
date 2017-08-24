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
	@build			24th August, 2017
	@created		28th June, 2016
	@package		Location Data
	@subpackage		ajax.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

/**
 * Locationdata Ajax Model
 */
class LocationdataModelAjax extends JModelList
{
	protected $app_params;

	public function __construct()
	{
		parent::__construct();
		// get params
		$this->app_params = JComponentHelper::getParams('com_locationdata');

	}

	// Used in exchangerates
	/**
	* 	updating the queue status check/force
	**/
	public function theQueue($time, $force, $updateThis)
	{
		// Get model
		$model = LocationdataHelper::getModel('updatedata', JPATH_COMPONENT_ADMINISTRATOR);
		// do update
		return $model->runQueue($time, $force, $updateThis);
	}

	/**
	* 	Get the data for this value
	**/
	public function getData($ipValue, $keyValue, $baseValue, $mValue, $sValue, $valueValue)
	{
		// return value from global helper function
		return LocationdataHelper::getLocationdata($ipValue, $keyValue, $baseValue, $mValue, $sValue, $valueValue);
	}

	protected $functionArray = array(
				'from' => 'loadCurrancyName',
				'to' => 'loadCurrancyName',
				'rate' => 'addCurrency',
				'ask' => 'addCurrency',
				'bid' => 'addCurrency',
				'date_rate' => 'fancyDateTime');

	protected $functionFilterArray = array(
				'date_rate' => 'toTimeStamp',
				'from' => 'loadCurrancyName',
				'to' => 'loadCurrancyName');

	protected $currency;
	protected $exchangeRate = null;

	/**
	* Get Headers of Exchange Rates data
	* 
	* @return    string    Formatted html table row
	*/
	public function getColumns(&$page)
	{
		// return columns
		return array(
			array( 'name' => 'name', 'title' => JText::_('COM_LOCATIONDATA_NAME'), 'type' => 'text', 'sorted' => true, 'direction' => 'ASC'),
			array( 'name' => 'from', 'title' => JText::_('COM_LOCATIONDATA_FROM'), 'type' => 'text', 'breakpoints' => 'sm'),
			array( 'name' => 'to', 'title' => JText::_('COM_LOCATIONDATA_TO'), 'type' => 'text', 'breakpoints' => 'sm'),
			array( 'name' => 'rate', 'title' => JText::_('COM_LOCATIONDATA_RATE'), 'type' => 'text', 'sort-use' => 'number'),
			array( 'name' => 'ask', 'title' => JText::_('COM_LOCATIONDATA_ASK'), 'type' => 'text', 'sort-use' => 'number', 'breakpoints' => 'xs sm'),
			array( 'name' => 'bid', 'title' => JText::_('COM_LOCATIONDATA_BID'), 'type' => 'text', 'sort-use' => 'number', 'breakpoints' => 'xs sm'),
			array( 'name' => 'date_rate', 'title' => JText::_('COM_LOCATIONDATA_DATE'), 'type' => 'text', 'sort-use' => 'number', 'breakpoints' => 'xs sm')
		);
	}

	/**
	* Get Rows of Exchange Rates data
	* 
	* @return    string    Formatted html table row
	*/
	public function getRows(&$key,&$page)
	{
		$session = JFactory::getSession();
		$exchangeRates = $session->get($key, null);
		// check if this is valid json
		if (LocationdataHelper::checkJson($exchangeRates))
		{
			$array = json_decode($exchangeRates, true);
			// at last lets get started
			if (LocationdataHelper::checkArray($array))
			{
				$items = $this->getItems($array);
				if ($items)
				{
					$rowArray = $this->getColumns($page);
					// start row builder
					$this->rows = array();
					foreach($items as $nr => $item)
					{
						// build the row
						$this->rows[$nr] = array();
						foreach($rowArray as $value)
						{
							if (isset($item->{$value['name']}))
							{
								// build a click-able button
								$this->setRow($nr, $value['name'], $item->{$value['name']});
							}
							else
							{
								$this->rows[$nr][$value['name']]['value'] = '-';
							}
						}
					}
					// just return this for now :)
					return $this->rows;
				}
			}
		}
		return false;
	}

	protected function setValue($header, $value)
	{
		if (array_key_exists($header, $this->functionArray) && method_exists($this, $this->functionArray[$header]))
		{
			$value = $this->{$this->functionArray[$header]}($header, $value);
		}
		// if no value is set
		if (!LocationdataHelper::checkString($value))
		{
			$value = '-';
		}
		return $value;
	}

	protected function setFilterValue($header, $value)
	{
		if (array_key_exists($header, $this->functionFilterArray) && method_exists($this, $this->functionFilterArray[$header]))
		{
			$value = $this->{$this->functionFilterArray[$header]}($header, $value);
		}
		return $value;
	}

	protected function setRow($nr, $header, $value)
	{
		// build rows
		$this->rows[$nr][$header]['value'] =  $this->setValue($header, $value);
		$this->rows[$nr][$header]['options']  = array('filterValue' => $this->setFilterValue($header, $value));
	}

	protected $currancyName = array();

	protected function loadCurrancyName($header, $value)
	{
		// set currency for the conversion
		if ($header == 'to')
		{
			$this->currency = $value;
		}
		// only get if new
		if (!isset($this->currancyName[$value]))
		{
			$this->currancyName[$value] = LocationdataHelper::getVar('currency', $value, 'codethree', 'name');
		}
		return $this->currancyName[$value];
	}
	
	protected function addCurrency($header, $value)
	{
		return LocationdataHelper::addCurrency($value, $this->currency);
	}

	protected function fancyDateTime($header, $value)
	{
		return LocationdataHelper::fancyDateTime($value);
	}

	protected function toTimeStamp($header, $value)
	{
		return strtotime($value);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Make sure all records load, since no pagination allowed.
		$this->setState('list.limit', 0);
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// get Columns
		$page = 'internal';
		$columns = $this->getColumns($page);
		$select = array();
		foreach ($columns as $column)
		{
			$select[] = 'a.'.$column['name'];
		}
		// Get from #__locationdata_exchange_rate as a
		$query->select($db->quoteName($select));
		$query->from($db->quoteName('#__locationdata_exchange_rate', 'a'));

		// Get only selected ids
		if ($this->exchangeRate)
		{
			$query->where('a.id IN ('. implode(',',$this->exchangeRate).')' );
		}
		$query->order('a.name ASC');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems($ids = null)
	{
		// we could add this check point later (TODO)
		// $user = JFactory::getUser();
		// check if this user has permission to access items
		// if (!$user->authorise('site.exchangerates.access', 'com_locationdata'))
		// {
		// 	return false;
		// }
		// load the ids if set
		if ($ids)
		{
			$this->exchangeRate = $ids;
		}
		// load parent items
		return parent::getItems();
	} 
}
