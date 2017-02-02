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
	@subpackage		default_vdm.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$manifest = LocationdataHelper::manifest();
JHtml::_('bootstrap.loadCss');

?>
<img alt="<?php echo JText::_('COM_LOCATIONDATA'); ?>" src="components/com_locationdata/assets/images/component-300.jpg">
<ul class="list-striped">
<li><b><?php echo JText::_('COM_LOCATIONDATA_VERSION'); ?>:</b> <?php echo $manifest->version; ?></li>
<li><b><?php echo JText::_('COM_LOCATIONDATA_DATE'); ?>:</b> <?php echo $manifest->creationDate; ?></li>
<li><b><?php echo JText::_('COM_LOCATIONDATA_AUTHOR'); ?>:</b> <a href="mailto:<?php echo $manifest->authorEmail; ?>"><?php echo $manifest->author; ?></a></li>
<li><b><?php echo JText::_('COM_LOCATIONDATA_WEBSITE'); ?>:</b> <a href="<?php echo $manifest->authorUrl; ?>" target="_blank"><?php echo $manifest->authorUrl; ?></a></li>
<li><b><?php echo JText::_('COM_LOCATIONDATA_LICENSE'); ?>:</b> <?php echo $manifest->license; ?></li>
<li><b><?php echo $manifest->copyright; ?></b></li>
</ul>
<div class="clearfix"></div>
<?php if(LocationdataHelper::checkArray($this->contributors)): ?>
<?php if(count($this->contributors) > 1): ?>
<h3><?php echo JText::_('COM_LOCATIONDATA_CONTRIBUTORS'); ?></h3>
<?php else: ?>
<h3><?php echo JText::_('COM_LOCATIONDATA_CONTRIBUTOR'); ?></h3>
<?php endif; ?>
<ul class="list-striped">
	<?php foreach($this->contributors as $contributor): ?>
    <li><b><?php echo $contributor['title']; ?>:</b> <?php echo $contributor['name']; ?></li>
    <?php endforeach; ?>
</ul>
<div class="clearfix"></div>
<?php endif; ?>