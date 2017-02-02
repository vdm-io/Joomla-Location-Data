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

	@version		@update number 7 of this MVC
	@build			14th January, 2017
	@created		28th June, 2016
	@package		Location Data
	@subpackage		ip_tables.php
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
 * Ip_tables Controller
 */
class LocationdataControllerIp_tables extends JControllerAdmin
{
	protected $text_prefix = 'COM_LOCATIONDATA_IP_TABLES';
	/**
	 * Proxy for getModel.
	 * @since	2.5
	 */
	public function getModel($name = 'Ip_table', $prefix = 'LocationdataModel', $config = array())
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
		if ($user->authorise('ip_table.export', 'com_locationdata') && $user->authorise('core.export', 'com_locationdata'))
		{
			// Get the input
			$input = JFactory::getApplication()->input;
			$pks = $input->post->get('cid', array(), 'array');
			// Sanitize the input
			JArrayHelper::toInteger($pks);
			// Get the model
			$model = $this->getModel('Ip_tables');
			// get the data to export
			$data = $model->getExportData($pks);
			if (LocationdataHelper::checkArray($data))
			{
				// now set the data to the spreadsheet
				$date = JFactory::getDate();
				LocationdataHelper::xls($data,'Ip_tables_'.$date->format('jS_F_Y'),'Ip tables exported ('.$date->format('jS F, Y').')','ip tables');
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_LOCATIONDATA_EXPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=ip_tables', false), $message, 'error');
		return;
	}


	public function importData()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// check if import is allowed for this user.
		$user = JFactory::getUser();
		if ($user->authorise('ip_table.import', 'com_locationdata') && $user->authorise('core.import', 'com_locationdata'))
		{
			// Get the import model
			$model = $this->getModel('Ip_tables');
			// get the headers to import
			$headers = $model->getExImPortHeaders();
			if (LocationdataHelper::checkObject($headers))
			{
				// Load headers to session.
				$session = JFactory::getSession();
				$headers = json_encode($headers);
				$session->set('ip_table_VDM_IMPORTHEADERS', $headers);
				$session->set('backto_VDM_IMPORT', 'ip_tables');
				$session->set('dataType_VDM_IMPORTINTO', 'ip_table');
				// Redirect to import view.
				$message = JText::_('COM_LOCATIONDATA_IMPORT_SELECT_FILE_FOR_IP_TABLES');
				$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=import', false), $message);
				return;
			}
		}
		// Redirect to the list screen with error.
		$message = JText::_('COM_LOCATIONDATA_IMPORT_FAILED');
		$this->setRedirect(JRoute::_('index.php?option=com_locationdata&view=ip_tables', false), $message, 'error');
		return;
	} 
}
