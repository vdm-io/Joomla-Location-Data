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
 * Ip_table View class
 */
class LocationdataViewIp_table extends JViewLegacy
{
	/**
	 * display method of View
	 * @return void
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
                {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Assign the variables
		$this->form 		= $this->get('Form');
		$this->item 		= $this->get('Item');
		$this->script 		= $this->get('Script');
		$this->state		= $this->get('State');
                // get action permissions
		$this->canDo		= LocationdataHelper::getActions('ip_table',$this->item);
		// get input
		$jinput = JFactory::getApplication()->input;
		$this->ref 		= $jinput->get('ref', 0, 'word');
		$this->refid            = $jinput->get('refid', 0, 'int');
		$this->referral         = '';
		if ($this->refid)
                {
                        // return to the item that refered to this item
                        $this->referral = '&ref='.(string)$this->ref.'&refid='.(int)$this->refid;
                }
                elseif($this->ref)
                {
                        // return to the list view that refered to this item
                        $this->referral = '&ref='.(string)$this->ref;
                }

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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		JToolbarHelper::title( JText::_($isNew ? 'COM_LOCATIONDATA_IP_TABLE_NEW' : 'COM_LOCATIONDATA_IP_TABLE_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if ($this->refid || $this->ref)
		{
			if ($this->canDo->get('ip_table.create') && $isNew)
			{
				// We can create the record.
				JToolBarHelper::save('ip_table.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('ip_table.edit'))
			{
				// We can save the record.
				JToolBarHelper::save('ip_table.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				JToolBarHelper::cancel('ip_table.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				JToolBarHelper::cancel('ip_table.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('ip_table.create'))
				{
					JToolBarHelper::apply('ip_table.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('ip_table.save', 'JTOOLBAR_SAVE');
					JToolBarHelper::custom('ip_table.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				JToolBarHelper::cancel('ip_table.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('ip_table.edit'))
				{
					// We can save the new record
					JToolBarHelper::apply('ip_table.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('ip_table.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('ip_table.create'))
					{
						JToolBarHelper::custom('ip_table.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				$canVersion = ($this->canDo->get('core.version') && $this->canDo->get('ip_table.version'));
				if ($this->state->params->get('save_history', 1) && $this->canDo->get('ip_table.edit') && $canVersion)
				{
					JToolbarHelper::versions('com_locationdata.ip_table', $this->item->id);
				}
				if ($this->canDo->get('ip_table.create'))
				{
					JToolBarHelper::custom('ip_table.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				JToolBarHelper::cancel('ip_table.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		JToolbarHelper::divider();
		// set help url for this view if found
		$help_url = LocationdataHelper::getHelpUrl('ip_table');
		if (LocationdataHelper::checkString($help_url))
		{
			JToolbarHelper::help('COM_LOCATIONDATA_HELP_MANAGER', false, $help_url);
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
		if(strlen($var) > 30)
		{
    		// use the helper htmlEscape method instead and shorten the string
			return LocationdataHelper::htmlEscape($var, $this->_charset, true, 30);
		}
                // use the helper htmlEscape method instead.
		return LocationdataHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew = ($this->item->id < 1);
		$document = JFactory::getDocument();
		$document->setTitle(JText::_($isNew ? 'COM_LOCATIONDATA_IP_TABLE_NEW' : 'COM_LOCATIONDATA_IP_TABLE_EDIT'));
		$document->addStyleSheet(JURI::root() . "administrator/components/com_locationdata/assets/css/ip_table.css"); 
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "administrator/components/com_locationdata/views/ip_table/submitbutton.js"); 
		JText::script('view not acceptable. Error');
	}
}
