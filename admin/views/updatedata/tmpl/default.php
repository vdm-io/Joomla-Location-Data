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
	@subpackage		default.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
?>
<?php if ($this->canDo->get('updatedata.access')): ?>
<?php $urlId = (isset($this->item->id)) ? '&id='. (int) $this->item->id : ''; ?>
<form action="<?php echo JRoute::_('index.php?option=com_locationdata&view=updatedata'.$urlId); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'updatedata.updateExchangeRates' || task == 'updatedata.updateIpData') {
		jQuery('#locationdata_loader').hide();
		jQuery('#loading').css('display', 'block');
		jQuery('#uploader').show();
	}
	Joomla.submitform(task);
	return true;
}

// Add spindle-wheel for update:
jQuery(document).ready(function($) {
	// waiting spinner
	var outerDiv = jQuery('body');
	jQuery('<div id="loading"></div>')
		.css("background", "rgba(255, 255, 255, .8) url('components/com_locationdata/assets/images/import.gif') 50% 15% no-repeat")
		.css("top", outerDiv.position().top - jQuery(window).scrollTop())
		.css("left", outerDiv.position().left - jQuery(window).scrollLeft())
		.css("width", outerDiv.width())
		.css("height", outerDiv.height())
		.css("position", "fixed")
		.css("opacity", "0.80")
		.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
		.css("filter", "alpha(opacity = 80)")
		.css("display", "none")
		.appendTo(outerDiv);
	jQuery('#loading').show();
	// when page is ready remove and show
	jQuery(window).load(function() {
		jQuery('#locationdata_loader').fadeIn('fast');
		jQuery('#loading').hide();
	});
});
</script>
<div id="locationdata_loader">
	<?php if ($this->lastexhangeupdate || $this->lastipupdate) : ?>
		<?php if ($this->lastexhangeupdate) : ?>
			<h1><?php echo JText::sprintf('COM_LOCATIONDATA_EXCHANGE_RATES_LAST_UPDATE_WAS_EMSEM', $this->lastexhangeupdate); ?></h1>
		<?php else: ?>
			<h2><?php echo JText::_('COM_LOCATIONDATA_THERE_IS_NO_EXCHANGE_RATES_IN_THE_DATABASE_CLICK_THE_UPDATE_OPTION_NOW'); ?></h2>
		<?php endif; ?>
		<?php if ($this->lastipupdate) : ?>
			<h1><?php echo JText::sprintf('COM_LOCATIONDATA_IP_TABLES_LAST_UPDATE_WAS_EMSEM', $this->lastipupdate); ?></h1>
		<?php else: ?>
			<h2><?php echo JText::_('COM_LOCATIONDATA_THERE_IS_NO_IP_TABLES_IN_THE_DATABASE_CLICK_THE_UPDATE_OPTION_NOW'); ?></h2>
		<?php endif; ?>
	<?php else: ?>
		<h2><?php echo JText::_('COM_LOCATIONDATA_THERE_IS_NO_IP_TABLES_OR_EXCHANGE_RATES_IN_THE_DATABASE_CLICK_ONE_OF_THE_UPDATE_OPTIONS_NOW'); ?></h2>
	<?php endif; ?>
	<h2 class="nav-header"><em><?php echo JText::_('COM_LOCATIONDATA_SUPPORT_THE_SOFTWARESEVENTY_SEVEN_TEAM'); ?></em></h2>
	<div class="well well-small">
		<p><?php echo JText::_('COM_LOCATIONDATA_THE_DATABASE_USED_TO_UPDATED_IP_TABLES_IS_PROVIDED_BY'); ?> <a target="_blank" href="http://software77.net/geo-ip/"><?php echo JText::_('COM_LOCATIONDATA_SOFTWARESEVENTY_SEVENNET'); ?></a> <?php echo JText::_('COM_LOCATIONDATA_AS'); ?> "<a target="_blank" href="http://software77.net/geo-ip/?license"><?php echo JText::_('COM_LOCATIONDATA_DONATIONWARE'); ?></a>" <br><?php echo JText::_('COM_LOCATIONDATA_THEY_NEED_YOUR_SUPPORT_PLEASE_MAKE_A'); ?> <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3HKM8N5XXUHV6"><?php echo JText::_('COM_LOCATIONDATA_DONATION_VIA_PAYPAL'); ?></a> <?php echo JText::_('COM_LOCATIONDATA_NOW'); ?>!</p>
	</div>
	<h2 class="nav-header"><em><?php echo JText::_('COM_LOCATIONDATA_YAHOO_FINANCE'); ?> & <?php echo JText::_('VDM'); ?></em></h2>
	<div class="well well-small">
		<p><?php echo JText::_('COM_LOCATIONDATA_THANKS_TO'); ?> <a target="_blank" href="http://finance.yahoo.com/currency-converter/"><?php echo JText::_('COM_LOCATIONDATA_YAHOO_FINANCE'); ?></a> and  <a target="_blank" href="https://github.com/ExchangeRates/README"><?php echo JText::_('COM_LOCATIONDATA_VAST_DEVELOPMENT_METHOD'); ?></a> <?php echo JText::_('COM_LOCATIONDATA_WE_CAN_UPDATE_YOUR_DB_WITH_THE_LATEST_EXCHANGE_RATES'); ?></p>
		<p><?php echo JText::_('COM_LOCATIONDATA_VDM_CREATED_A'); ?> <a target="_blank" href="https://github.com/ExchangeRates/Factory"><?php echo JText::_('COM_LOCATIONDATA_SHELL_SCRIPT'); ?></a> <?php echo JText::_('that updates a github repository with Yahoo\'s latest rates in a very methodical, stable and accurate way.'); ?> <?php echo JText::_('COM_LOCATIONDATA_THIS_IS_THE'); ?> <a target="_blank" href="https://raw.githubusercontent.com/ExchangeRates/yahoo/master/rates.json"><?php echo JText::_('COM_LOCATIONDATA_JSON'); ?></a> <?php echo JText::_('COM_LOCATIONDATA_FILE_WE_USED_DURING_EACH_UPDATE_OF_ALL_YOUR_LOCAL_EXCHANGE_RATES_THEREFORE_UPDATES_ARE_VERY_EASY_AND_EXTREMELY_FAST'); ?></p>
		<p><?php echo JText::_('COM_LOCATIONDATA_VDM_NEEDS_YOUR_SUPPORT_PLEASE_MAKE_A_DONATION_VIA'); ?> <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LC83G3UD9W34N"><?php echo JText::_('COM_LOCATIONDATA_PAYPAL'); ?></a> or BITCOIN <code> 3H35PYwikEosvcjxHXGPLS1GufZ4b4iFu3</code> <?php echo JText::_('COM_LOCATIONDATA_NOW'); ?>!</p>
	</div>
</div>
<div id="uploader" style="display:none;">
	<center><h1><?php echo JText::_('COM_LOCATIONDATA_DO_NOT_CLOSE_THE_BROWSER_WINDOWBR_PLEASE_WAIT'); ?><span class="loading-dots">.</span></h1></center>
</div>
<script>
// nice little dot trick :)
jQuery(document).ready( function($) {
  var x=0;
  setInterval(function() {
	var dots = "";
	x++;
	for (var y=0; y < x%8; y++) {
		dots+=".";
	}
	$(".loading-dots").text(dots);
  } , 500);
});
</script>
<?php else: ?>
        <h1><?php echo JText::_('COM_LOCATIONDATA_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>

