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
	@build			22nd August, 2017
	@created		28th June, 2016
	@package		Location Data
	@subpackage		controller.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of Locationdata component
 */
class LocationdataController extends JControllerLegacy
{
	/**
	 * display task
	 *
	 * @return void
	 */
        function display($cachable = false, $urlparams = false)
	{
		// set default view if not set
		$view   = $this->input->getCmd('view', 'Locationdata');
		$data	= $this->getViewRelation($view);
		$layout	= $this->input->get('layout', null, 'WORD');
		$id    	= $this->input->getInt('id');

		// Check for edit form.
                if(LocationdataHelper::checkArray($data))
                {
                    if ($data['edit'] && $layout == 'edit' && !$this->checkEditId('com_locationdata.edit.'.$data['view'], $id))
                    {
                        // Somehow the person just went to the form - we don't allow that.
                        $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
                        $this->setMessage($this->getError(), 'error');
                        // check if item was opend from other then its own list view
                        $ref 	= $this->input->getCmd('ref', 0);
                        $refid 	= $this->input->getInt('refid', 0);
                        // set redirect
                        if ($refid > 0 && LocationdataHelper::checkString($ref))
                        {
                            // redirect to item of ref
                            $this->setRedirect(JRoute::_('index.php?option=com_locationdata&view='.(string)$ref.'&layout=edit&id='.(int)$refid, false));
                        }
                        elseif (LocationdataHelper::checkString($ref))
                        {

                            // redirect to ref
                            $this->setRedirect(JRoute::_('index.php?option=com_locationdata&view='.(string)$ref, false));
                        }
                        else
                        {
                            // normal redirect back to the list view
                            $this->setRedirect(JRoute::_('index.php?option=com_locationdata&view='.$data['views'], false));
                        }

                        return false;
                    }
                }

		return parent::display($cachable, $urlparams);
	}

	protected function getViewRelation($view)
	{
                if (LocationdataHelper::checkString($view))
                {
                        $views = array(
				'country' => 'countries',
				'currency' => 'currencies',
				'exchange_rate' => 'exchange_rates',
				'ip_table' => 'ip_tables'
                                );
                        // check if this is a list view
                        if (in_array($view,$views))
                        {
                            return array('edit' => false, 'view' => array_search($view,$views), 'views' => $view);
                        }
                        // check if it is an edit view
                        elseif (array_key_exists($view,$views))
                        {
                                return array('edit' => true, 'view' => $view, 'views' => $views[$view]);
                        }
                }
		return false;
	}
}
