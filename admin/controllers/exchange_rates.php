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

	@version		@update number 37 of this MVC
	@build			30th October, 2016
	@created		28th June, 2016
	@package		Location Data
	@subpackage		exchange_rates.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * Exchange_rates Controller
 */
class LocationdataControllerExchange_rates extends JControllerAdmin
{
	protected $text_prefix = 'COM_LOCATIONDATA_EXCHANGE_RATES';
	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'Exchange_rate', $prefix = 'LocationdataModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		
		return $model;
	}

	public function exportData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if export is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('exchange_rate.export', 'com_locationdata') && $user->authorise('core.export', 'com_locationdata'))
		{
			// Get the input
			$input = JFactory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			JArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Exchange_rates');
			// get the data to export
			$data = $model->getExportData($pks);
			if (LocationdataHelper::checkArray($data))
			{
				// now set the data to the spreadsheet
				$date = JFactory::getDate();
				LocationdataHelper::xls($data,'Exchange_rates_'.$date->format('jS_F_Y'),'Exchange rates exported ('.$date->format('jS F, Y').')','exchange rates');
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_LOCATIONDATA_EXPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=exchange_rates', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('exchange_rate.import', 'com_locationdata') && $user->authorise('core.import', 'com_locationdata'))
		{
			// Get the import model
			$model = $this->getModel('Exchange_rates');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (LocationdataHelper::checkObject($headers))
			{
				// Load headers to session.
				$session = JFactory::getSession();
				$headers = json_encode($headers);
				$session->set('exchange_rate_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'exchange_rates');
				$session->set('dataType_VDM_IMPORTINTO', 'exchange_rate');
				// Redirect to import view.
				$message = JText::_('COM_LOCATIONDATA_IMPORT_SELECT_FILE_FOR_EXCHANGE_RATES');
				$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_LOCATIONDATA_IMPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=exchange_rates', false), $message, 'error');
		return;
	} 
}
