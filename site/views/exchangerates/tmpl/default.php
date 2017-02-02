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
	@build			18th January, 2017
	@created		14th August, 2016
	@package		Location Data
	@subpackage		default.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 

?>
<?php echo $this->toolbar->render(); ?> 
<?php if (isset($this->items) && LocationdataHelper::checkArray($this->items)): ?>
	<table id="table" class="footable uk-table" data-show-toggle="true" data-toggle-column="first" data-paging="true" data-filtering="true" data-paging-size="100" data-sorting="true"></table>
	<script type="text/javascript">
		// page name
		var pageName = 'ExchangeRates';
		// token 
		var token = '<?php echo JSession::getFormToken(); ?>';
		// set the key
		var key = '<?php echo $this->exchangeRatesBundlesKey; ?>';
		// the get url
		var columnsUrl = "<?php echo JURI::root(); ?>index.php?option=com_locationdata&task=ajax.getColumns&format=json&raw=true&page=ExchangeRates&token="+token;
		var rowsUrl = "<?php echo JURI::root(); ?>index.php?option=com_locationdata&task=ajax.getRows&format=json&raw=true&page=ExchangeRates&token="+token+"&key="+key;
		jQuery(function($){
			$('.footable').footable({
				"columns": $.get(columnsUrl),
				"rows":  $.get(rowsUrl),
			});
		});
	</script>
<?php else: ?>
	<div class="uk-alert"><?php echo JText::_('COM_LOCATIONDATA_NO_EXCHANGE_RATES_FOUND'); ?></div>
<?php endif; ?>	  
