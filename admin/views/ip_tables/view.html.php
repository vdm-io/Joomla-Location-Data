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
 * Locationdata View class for the Ip_tables
 */
class LocationdataViewIp_tables extends JViewLegacy
{
	/**
	 * Ip_tables view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			LocationdataHelper::addSubmenu('ip_tables');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
                {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Assign data to the view
		$this->items 		= $this->get('Items');
		$this->pagination 	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->user 		= JFactory::getUser();
		$this->listOrder	= $this->escape($this->state->get('list.ordering'));
		$this->listDirn		= $this->escape($this->state->get('list.direction'));
		$this->saveOrder	= $this->listOrder == 'ordering';
                // get global action permissions
		$this->canDo		= LocationdataHelper::getActions('ip_table');
		$this->canEdit		= $this->canDo->get('ip_table.edit');
		$this->canState		= $this->canDo->get('ip_table.edit.state');
		$this->canCreate	= $this->canDo->get('ip_table.create');
		$this->canDelete	= $this->canDo->get('ip_table.delete');
		$this->canBatch	= $this->canDo->get('core.batch');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
                        // load the batch html
                        if ($this->canCreate && $this->canEdit && $this->canState)
                        {
                                $this->batchDisplay = JHtmlBatch_::render();
                        }
		}

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
		JToolBarHelper::title(JText::_('COM_LOCATIONDATA_IP_TABLES'), 'archive');
		JHtmlSidebar::setAction('index.php?option=com_locationdata&view=ip_tables');
                JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

		if ($this->canCreate)
                {
			JToolBarHelper::addNew('ip_table.add');
		}

                // Only load if there are items
                if (LocationdataHelper::checkArray($this->items))
		{
                        if ($this->canEdit)
                        {
                            JToolBarHelper::editList('ip_table.edit');
                        }

                        if ($this->canState)
                        {
                            JToolBarHelper::publishList('ip_tables.publish');
                            JToolBarHelper::unpublishList('ip_tables.unpublish');
                            JToolBarHelper::archiveList('ip_tables.archive');

                            if ($this->canDo->get('core.admin'))
                            {
                                JToolBarHelper::checkin('ip_tables.checkin');
                            }
                        }

                        // Add a batch button
                        if ($this->canBatch && $this->canCreate && $this->canEdit && $this->canState)
                        {
                                // Get the toolbar object instance
                                $bar = JToolBar::getInstance('toolbar');
                                // set the batch button name
                                $title = JText::_('JTOOLBAR_BATCH');
                                // Instantiate a new JLayoutFile instance and render the batch button
                                $layout = new JLayoutFile('joomla.toolbar.batch');
                                // add the button to the page
                                $dhtml = $layout->render(array('title' => $title));
                                $bar->appendButton('Custom', $dhtml, 'batch');
                        } 

                        if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete))
                        {
                            JToolbarHelper::deleteList('', 'ip_tables.delete', 'JTOOLBAR_EMPTY_TRASH');
                        }
                        elseif ($this->canState && $this->canDelete)
                        {
                                JToolbarHelper::trash('ip_tables.trash');
                        }

			if ($this->canDo->get('core.export') && $this->canDo->get('ip_table.export'))
			{
				JToolBarHelper::custom('ip_tables.exportData', 'download', '', 'COM_LOCATIONDATA_EXPORT_DATA', true);
			}
                } 

		if ($this->canDo->get('core.import') && $this->canDo->get('ip_table.import'))
		{
			JToolBarHelper::custom('ip_tables.importData', 'upload', '', 'COM_LOCATIONDATA_IMPORT_DATA', false);
		}

                // set help url for this view if found
                $help_url = LocationdataHelper::getHelpUrl('ip_tables');
                if (LocationdataHelper::checkString($help_url))
                {
                        JToolbarHelper::help('COM_LOCATIONDATA_HELP_MANAGER', false, $help_url);
                }

                // add the options comp button
                if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
                {
                        JToolBarHelper::preferences('com_locationdata');
                }

                if ($this->canState)
                {
			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
			);
                        // only load if batch allowed
                        if ($this->canBatch)
                        {
                            JHtmlBatch_::addListSelection(
                                JText::_('COM_LOCATIONDATA_KEEP_ORIGINAL_STATE'),
                                'batch[published]',
                                JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('all' => false)), 'value', 'text', '', true)
                            );
                        }
		}

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			JHtmlBatch_::addListSelection(
                                JText::_('COM_LOCATIONDATA_KEEP_ORIGINAL_ACCESS'),
                                'batch[access]',
                                JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text')
			);
                }  

		// Set Protocol Selection
		$this->protocolOptions = $this->getTheProtocolSelections();
		if ($this->protocolOptions)
		{
			// Protocol Filter
			JHtmlSidebar::addFilter(
				'- Select '.JText::_('COM_LOCATIONDATA_IP_TABLE_PROTOCOL_LABEL').' -',
				'filter_protocol',
				JHtml::_('select.options', $this->protocolOptions, 'value', 'text', $this->state->get('filter.protocol'))
			);

			if ($this->canBatch && $this->canCreate && $this->canEdit)
			{
				// Protocol Batch Selection
				JHtmlBatch_::addListSelection(
					'- Keep Original '.JText::_('COM_LOCATIONDATA_IP_TABLE_PROTOCOL_LABEL').' -',
					'batch[protocol]',
					JHtml::_('select.options', $this->protocolOptions, 'value', 'text')
				);
			}
		}

		// Set Registry Selection
		$this->registryOptions = $this->getTheRegistrySelections();
		if ($this->registryOptions)
		{
			// Registry Filter
			JHtmlSidebar::addFilter(
				'- Select '.JText::_('COM_LOCATIONDATA_IP_TABLE_REGISTRY_LABEL').' -',
				'filter_registry',
				JHtml::_('select.options', $this->registryOptions, 'value', 'text', $this->state->get('filter.registry'))
			);

			if ($this->canBatch && $this->canCreate && $this->canEdit)
			{
				// Registry Batch Selection
				JHtmlBatch_::addListSelection(
					'- Keep Original '.JText::_('COM_LOCATIONDATA_IP_TABLE_REGISTRY_LABEL').' -',
					'batch[registry]',
					JHtml::_('select.options', $this->registryOptions, 'value', 'text')
				);
			}
		}

		// Set Cntry Codethree Selection
		$this->cntryCodethreeOptions = JFormHelper::loadFieldType('Cntry')->getOptions();
		if ($this->cntryCodethreeOptions)
		{
			// Cntry Codethree Filter
			JHtmlSidebar::addFilter(
				'- Select '.JText::_('COM_LOCATIONDATA_IP_TABLE_CNTRY_LABEL').' -',
				'filter_cntry',
				JHtml::_('select.options', $this->cntryCodethreeOptions, 'value', 'text', $this->state->get('filter.cntry'))
			);

			if ($this->canBatch && $this->canCreate && $this->canEdit)
			{
				// Cntry Codethree Batch Selection
				JHtmlBatch_::addListSelection(
					'- Keep Original '.JText::_('COM_LOCATIONDATA_IP_TABLE_CNTRY_LABEL').' -',
					'batch[cntry]',
					JHtml::_('select.options', $this->cntryCodethreeOptions, 'value', 'text')
				);
			}
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
		$document->setTitle(JText::_('COM_LOCATIONDATA_IP_TABLES'));
		$document->addStyleSheet(JURI::root() . "administrator/components/com_locationdata/assets/css/ip_tables.css");
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
		if(strlen($var) > 50)
		{
                        // use the helper htmlEscape method instead and shorten the string
			return LocationdataHelper::htmlEscape($var, $this->_charset, true);
		}
                // use the helper htmlEscape method instead.
		return LocationdataHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
			'a.sorting' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.ip_from' => JText::_('COM_LOCATIONDATA_IP_TABLE_IP_FROM_LABEL'),
			'a.ip_to' => JText::_('COM_LOCATIONDATA_IP_TABLE_IP_TO_LABEL'),
			'a.protocol' => JText::_('COM_LOCATIONDATA_IP_TABLE_PROTOCOL_LABEL'),
			'a.registry' => JText::_('COM_LOCATIONDATA_IP_TABLE_REGISTRY_LABEL'),
			'g.codethree' => JText::_('COM_LOCATIONDATA_IP_TABLE_CNTRY_LABEL'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	} 

	protected function getTheProtocolSelections()
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('protocol'));
		$query->from($db->quoteName('#__locationdata_ip_table'));
		$query->order($db->quoteName('protocol') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$results = $db->loadColumn();

		if ($results)
		{
			// get model
			$model = $this->getModel();
			$results = array_unique($results);
			$_filter = array();
			foreach ($results as $protocol)
			{
				// Translate the protocol selection
				$text = $model->selectionTranslation($protocol,'protocol');
				// Now add the protocol and its text to the options array
				$_filter[] = JHtml::_('select.option', $protocol, JText::_($text));
			}
			return $_filter;
		}
		return false;
	}

	protected function getTheRegistrySelections()
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('registry'));
		$query->from($db->quoteName('#__locationdata_ip_table'));
		$query->order($db->quoteName('registry') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$results = $db->loadColumn();

		if ($results)
		{
			$results = array_unique($results);
			$_filter = array();
			foreach ($results as $registry)
			{
				// Now add the registry and its text to the options array
				$_filter[] = JHtml::_('select.option', $registry, $registry);
			}
			return $_filter;
		}
		return false;
	}
}
