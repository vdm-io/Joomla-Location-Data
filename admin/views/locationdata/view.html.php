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
 * Locationdata View class
 */
class LocationdataViewLocationdata extends JViewLegacy
{
	/**
	 * View display method
	 * @return void
	 */
	function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
                {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		};
		// Assign data to the view
		$this->icons			= $this->get('Icons');
		$this->contributors		= LocationdataHelper::getContributors();
		$this->github	= $this->get('Github');
		$this->wiki	= $this->get('Wiki');
		$this->noticeboard	= $this->get('Noticeboard');
		$this->readme	= $this->get('Readme');
		
		// get the manifest details of the component
		$this->manifest = LocationdataHelper::manifest();
		
		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		$canDo = LocationdataHelper::getActions('locationdata');
		JToolBarHelper::title(JText::_('COM_LOCATIONDATA_DASHBOARD'), 'grid-2');

                // set help url for this view if found
                $help_url = LocationdataHelper::getHelpUrl('locationdata');
                if (LocationdataHelper::checkString($help_url))
                {
			JToolbarHelper::help('COM_LOCATIONDATA_HELP_MANAGER', false, $help_url);
                }

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
                {
			JToolBarHelper::preferences('com_locationdata');
		}
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$document = JFactory::getDocument();
		
		// add dashboard style sheets
		$document->addStyleSheet(JURI::root() . "administrator/components/com_locationdata/assets/css/dashboard.css");
		
		// set page title
		$document->setTitle(JText::_('COM_LOCATIONDATA_DASHBOARD'));
		
		// add manifest to page JavaScript
		$document->addScriptDeclaration("var manifest = jQuery.parseJSON('" . json_encode($this->manifest) . "');", "text/javascript");
	}
}
