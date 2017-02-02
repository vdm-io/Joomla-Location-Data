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
	@subpackage		view.html.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Locationdata View class for the Updatedata
 */
class LocationdataViewUpdatedata extends JViewLegacy
{
	// Overwriting JView display method
	function display($tpl = null)
	{
                // get component params
		$this->params	= JComponentHelper::getParams('com_locationdata');
		// get the application
		$this->app	= JFactory::getApplication();
		// get the user object
		$this->user	= JFactory::getUser();
                // get global action permissions
		$this->canDo	= LocationdataHelper::getActions('updatedata');
		// Initialise variables.
		$this->item	= $this->get('Item');
		$this->lastexhangeupdate	= $this->get('LastExhangeUpdate');
		$this->lastipupdate	= $this->get('LastIpUpdate');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$today = JFactory::getDate()->toUnix();
		// check if there is any exchange date
		if (LocationdataHelper::checkObject($this->lastexhangeupdate))
		{
			$past = bcsub($today, JFactory::getDate($this->lastexhangeupdate->created)->toUnix());
			$this->lastexhangeupdate = $this->setTimePast($past);
		}
		// check if there is any ip date
		if (LocationdataHelper::checkObject($this->lastipupdate))
		{
			$past = bcsub($today, JFactory::getDate($this->lastipupdate->created)->toUnix());
			$this->lastipupdate = $this->setTimePast($past);
		}

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			// add the tool bar
			$this->addToolBar();
		}

		// set the document
		$this->setDocument();

		parent::display($tpl);
	}

function setTimePast($seconds)
{
	if ($seconds > 60)
	{
		// convert to minutes
		$minutes = bcdiv($seconds, 60);
		if ($minutes > 60)
		{
			// convert to hours
			$hours = bcdiv($minutes, 60);
			if ($hours > 24)
			{
				// convert to days
				$days = bcdiv($hours, 24);
				if ($days > 31)
				{
					// convert to Months
					$months = bcdiv($days, 31);
					return $this->getAgo($months, 'mo');
				}
				else
				{
					// just a few days ago
					return $this->getAgo($days, 'd');
				}
			}
			else
			{
				// just a few hours ago
				return $this->getAgo($hours, 'h');
			}
		}
		else
		{
			// just a few minutes ago
			return $this->getAgo($minutes, 'm');
		}
	}
	else
	{
		// just a few seconds ago
		return $this->getAgo($seconds, 's');
	}
}

function getAgo($amount, $type)
{
	if ($amount > 1)
	{
		switch ($type)
		{
			case 's':
			$type = JText::_('COM_LOCATIONDATA_SECONDS');
			break;
			case 'm':
			$type = JText::_('COM_LOCATIONDATA_MINUTES');
			break;
			case 'h':
			$type = JText::_('COM_LOCATIONDATA_HOURS');
			break;
			case 'd':
			$type = JText::_('COM_LOCATIONDATA_DAYS');
			break;
			case 'mo':
			$type = JText::_('COM_LOCATIONDATA_MONTHS');
			break;
		}
	}
	else
	{
		switch ($type)
		{
			case 's':
			$type = JText::_('COM_LOCATIONDATA_SECOND');
			break;
			case 'm':
			$type = JText::_('COM_LOCATIONDATA_MINUTE');
			break;
			case 'h':
			$type = JText::_('COM_LOCATIONDATA_HOUR');
			break;
			case 'd':
			$type = JText::_('COM_LOCATIONDATA_DAY');
			break;
			case 'mo':
			$type = JText::_('COM_LOCATIONDATA_MONTH');
			break;
		}
	}
	return  JText::sprintf('COM_LOCATIONDATA_S_S_AGO', $amount, $type);
}

        /**
	 * Prepares the document
	 */
	protected function setDocument()
	{ 

		// always make sure jquery is loaded.
		JHtml::_('jquery.framework');
		// Load the header checker class.
		require_once( JPATH_COMPONENT_ADMINISTRATOR.'/helpers/headercheck.php' );
		// Initialize the header checker.
		$HeaderCheck = new HeaderCheck;

		// Load uikit options.
		$uikit = $this->params->get('uikit_load');
		// Set script size.
		$size = $this->params->get('uikit_min');
		// Set css style.
		$style = $this->params->get('uikit_style');

		// The uikit css.
		if ((!$HeaderCheck->css_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
		{
			$this->document->addStyleSheet(JURI::root(true) .'/media/com_locationdata/uikit/css/uikit'.$style.$size.'.css');
		}
		// The uikit js.
		if ((!$HeaderCheck->js_loaded('uikit.min') || $uikit == 1) && $uikit != 2 && $uikit != 3)
		{
			$this->document->addScript(JURI::root(true) .'/media/com_locationdata/uikit/js/uikit'.$size.'.js');
		}   
                // add the document default css file
		$this->document->addStyleSheet(JURI::root(true) .'/administrator/components/com_locationdata/assets/css/updatedata.css'); 
        }

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		// hide the main menu
		$this->app->input->set('hidemainmenu', true);
		// set the title
		if (isset($this->item->name) && $this->item->name)
		{
			$title = $this->item->name;
		}
		// Check for empty title and add view name if param is set
		if (empty($title))
		{
			$title = JText::_('COM_LOCATIONDATA_UPDATEDATA');
		}
		// add title to the page
		JToolbarHelper::title($title,'cogs');
                // add the back button
                // JToolBarHelper::custom('updatedata.back', 'undo-2', '', 'COM_LOCATIONDATA_BACK', false);
                // add cpanel button
		JToolBarHelper::custom('updatedata.dashboard', 'grid-2', '', 'COM_LOCATIONDATA_DASH', false);
		if ($this->canDo->get('updatedata.update_exchange_rates'))
		{
			// add Update Exchange Rates button.
			JToolBarHelper::custom('updatedata.updateExchangeRates', 'cog', '', 'COM_LOCATIONDATA_UPDATE_EXCHANGE_RATES', false);
		}
		if ($this->canDo->get('updatedata.update_ips'))
		{
			// add Update IP's button.
			JToolBarHelper::custom('updatedata.updateIpData', 'cog', '', 'COM_LOCATIONDATA_UPDATE_IPS', false);
		}
		if ($this->canDo->get('updatedata.countries'))
		{
			// add Countries button.
			JToolBarHelper::custom('updatedata.gotoCountries', 'flag-3', '', 'COM_LOCATIONDATA_COUNTRIES', false);
		}
		if ($this->canDo->get('updatedata.currencies'))
		{
			// add Currencies button.
			JToolBarHelper::custom('updatedata.gotoCurrencies', 'credit-2', '', 'COM_LOCATIONDATA_CURRENCIES', false);
		}
		if ($this->canDo->get('updatedata.exchange_rates'))
		{
			// add Exchange Rates button.
			JToolBarHelper::custom('updatedata.gotoExchangeRates', 'screwdriver', '', 'COM_LOCATIONDATA_EXCHANGE_RATES', false);
		}
		if ($this->canDo->get('updatedata.ip_tables'))
		{
			// add IP Tables button.
			JToolBarHelper::custom('updatedata.gotoIPtables', 'archive', '', 'COM_LOCATIONDATA_IP_TABLES', false);
		}

		// set help url for this view if found
                $help_url = LocationdataHelper::getHelpUrl('updatedata');
                if (LocationdataHelper::checkString($help_url))
                {
			JToolbarHelper::help('COM_LOCATIONDATA_HELP_MANAGER', false, $help_url);
                }

                // add the options comp button
                if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_locationdata');
		}
	}

        /**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
                // use the helper htmlEscape method instead.
		return LocationdataHelper::htmlEscape($var, $this->_charset);
	}
}
?>