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
	@build			2nd February, 2017
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
		$this->app_params	= JComponentHelper::getParams('com_locationdata');
		
	}

	// Used in exchange_rate
	/**
	* 	updating the queue status check/force
	**/
	public function theQueue($time, $force, $updateThis)
	{
		// Get model
		$model = LocationdataHelper::getModel('updatedata');
		// do update
		return $model->runQueue($time, $force, $updateThis);
	}

	/**
	* 	Check and if a vdm notice is new (per/user)
	**/
	public function isNew($notice)
	{
		// first get the file path
		$path_filename = LocationdataHelper::getFilePath('user', 'notice', JFactory::getUser()->username, $fileType = '.md', JPATH_COMPONENT_ADMINISTRATOR);
		// check if the file is set
		if (($content = @file_get_contents($path_filename)) !== FALSE)
		{
			if ($notice == $content)
			{
				return false;
			}
		}
		return true;
	}

	/**
	* 	set That a notice has been read (per/user)
	**/
	public function isRead($notice)
	{
		// first get the file path
		$path_filename = LocationdataHelper::getFilePath('user', 'notice', JFactory::getUser()->username, $fileType = '.md', JPATH_COMPONENT_ADMINISTRATOR);
		// set as read if not already set
		if (($content = @file_get_contents($path_filename)) !== FALSE)
		{
			if ($notice == $content)
			{
				return true;
			}
		}
		return $this->saveFile($notice,$path_filename);
	}

	protected function saveFile($data,$path_filename)
	{
		if (LocationdataHelper::checkString($data))
		{
			$fp = fopen($path_filename, 'w');
			fwrite($fp, $data);
			fclose($fp);
			return true;
		}
		return false;
	}
}
