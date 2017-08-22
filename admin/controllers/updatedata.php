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

	@version		@update number 102 of this MVC
	@build			22nd August, 2017
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

// import Joomla controllerform library
jimport('joomla.application.component.controller');

/**
 * Locationdata Updatedata Controller
 */
class LocationdataControllerUpdatedata extends JControllerLegacy
{
	public function __construct($config)
	{
		parent::__construct($config);
	}

        public function dashboard()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata', false));
		return;
	}

	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'Updatedata', $prefix = 'locationdataModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		
		return $model;
	}

        public function gotoCountries()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=countries', false));
		return;
	}

        public function gotoCurrencies()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=currencies', false));
		return;
	}

        public function gotoExchangeRates()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=exchange_rates', false));
		return;
	}

        public function gotoIPtables()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=ip_tables', false));
		return;
	}

	public function updateExchangeRates()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Get model
		$model = $this->getModel();
		// force update
		$force = 2;
		// time now
		$time = JFactory::getDate()->toUnix();
		// do update
		if($model->runQueue($time, $force, 'exchange_rate'))
		{
			// set message and redirect
			$this->setRedirect('index.php?option=com_locationdata&view=updatedata',JText::_('COM_LOCATIONDATA_EXCHANGE_RATES_WAS_SUCCESSFULLY_UPDATED'));
			return true;
		}
		// set message and redirect
		$this->setRedirect('index.php?option=com_locationdata&view=updatedata',JText::_('COM_LOCATIONDATA_UPDATE_FAILED_PLEASE_CHECK_THAT_THERE_ARE_CURRENCIES_PUBLISHED'), 'error');
		return false;
	}

	public function updateIpData()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Import dependencies
		jimport('joomla.filesystem.file');
		$model = $this->getModel();
		// force update
		$force = 2;
		// time now
		$time = JFactory::getDate()->toUnix();
		// upload ip Data
		if ($model->runQueue($time, $force, 'ip_table'))
		{
			$this->setRedirect('index.php?option=com_locationdata&view=updatedata',JText::_('COM_LOCATIONDATA_IP_TABLE_WAS_SUCCESSFULLY_UPDATED'));
			return true;
		}
		$this->setRedirect('index.php?option=com_locationdata&view=updatedata',JText::_('COM_LOCATIONDATA_THERE_WAS_AN_ERROR_PLEASE_TRY_AGAIN'), 'error');
		return false;
	}
}
