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
	@build			23rd August, 2017
	@created		28th June, 2016
	@package		Location Data
	@subpackage		script.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');

/**
 * Script File of Locationdata Component
 */
class com_locationdataInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{

	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		// Get Application object
		$app = JFactory::getApplication();

		// Get The Database object
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Country alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.country') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$country_found = $db->getNumRows();
		// Now check if there were any rows
		if ($country_found)
		{
			// Since there are load the needed  country type ids
			$country_ids = $db->loadColumn();
			// Remove Country from the content type table
			$country_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.country') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($country_condition);
			$db->setQuery($query);
			// Execute the query to remove Country items
			$country_done = $db->execute();
			if ($country_done);
			{
				// If succesfully remove Country add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.country) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Country items from the contentitem tag map table
			$country_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.country') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($country_condition);
			$db->setQuery($query);
			// Execute the query to remove Country items
			$country_done = $db->execute();
			if ($country_done);
			{
				// If succesfully remove Country add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.country) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Country items from the ucm content table
			$country_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_locationdata.country') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($country_condition);
			$db->setQuery($query);
			// Execute the query to remove Country items
			$country_done = $db->execute();
			if ($country_done);
			{
				// If succesfully remove Country add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.country) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Country items are cleared from DB
			foreach ($country_ids as $country_id)
			{
				// Remove Country items from the ucm base table
				$country_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $country_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($country_condition);
				$db->setQuery($query);
				// Execute the query to remove Country items
				$db->execute();

				// Remove Country items from the ucm history table
				$country_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $country_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($country_condition);
				$db->setQuery($query);
				// Execute the query to remove Country items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Currency alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.currency') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$currency_found = $db->getNumRows();
		// Now check if there were any rows
		if ($currency_found)
		{
			// Since there are load the needed  currency type ids
			$currency_ids = $db->loadColumn();
			// Remove Currency from the content type table
			$currency_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.currency') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($currency_condition);
			$db->setQuery($query);
			// Execute the query to remove Currency items
			$currency_done = $db->execute();
			if ($currency_done);
			{
				// If succesfully remove Currency add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.currency) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Currency items from the contentitem tag map table
			$currency_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.currency') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($currency_condition);
			$db->setQuery($query);
			// Execute the query to remove Currency items
			$currency_done = $db->execute();
			if ($currency_done);
			{
				// If succesfully remove Currency add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.currency) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Currency items from the ucm content table
			$currency_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_locationdata.currency') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($currency_condition);
			$db->setQuery($query);
			// Execute the query to remove Currency items
			$currency_done = $db->execute();
			if ($currency_done);
			{
				// If succesfully remove Currency add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.currency) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Currency items are cleared from DB
			foreach ($currency_ids as $currency_id)
			{
				// Remove Currency items from the ucm base table
				$currency_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $currency_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($currency_condition);
				$db->setQuery($query);
				// Execute the query to remove Currency items
				$db->execute();

				// Remove Currency items from the ucm history table
				$currency_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $currency_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($currency_condition);
				$db->setQuery($query);
				// Execute the query to remove Currency items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Exchange_rate alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.exchange_rate') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$exchange_rate_found = $db->getNumRows();
		// Now check if there were any rows
		if ($exchange_rate_found)
		{
			// Since there are load the needed  exchange_rate type ids
			$exchange_rate_ids = $db->loadColumn();
			// Remove Exchange_rate from the content type table
			$exchange_rate_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.exchange_rate') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($exchange_rate_condition);
			$db->setQuery($query);
			// Execute the query to remove Exchange_rate items
			$exchange_rate_done = $db->execute();
			if ($exchange_rate_done);
			{
				// If succesfully remove Exchange_rate add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.exchange_rate) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Exchange_rate items from the contentitem tag map table
			$exchange_rate_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.exchange_rate') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($exchange_rate_condition);
			$db->setQuery($query);
			// Execute the query to remove Exchange_rate items
			$exchange_rate_done = $db->execute();
			if ($exchange_rate_done);
			{
				// If succesfully remove Exchange_rate add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.exchange_rate) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Exchange_rate items from the ucm content table
			$exchange_rate_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_locationdata.exchange_rate') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($exchange_rate_condition);
			$db->setQuery($query);
			// Execute the query to remove Exchange_rate items
			$exchange_rate_done = $db->execute();
			if ($exchange_rate_done);
			{
				// If succesfully remove Exchange_rate add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.exchange_rate) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Exchange_rate items are cleared from DB
			foreach ($exchange_rate_ids as $exchange_rate_id)
			{
				// Remove Exchange_rate items from the ucm base table
				$exchange_rate_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $exchange_rate_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($exchange_rate_condition);
				$db->setQuery($query);
				// Execute the query to remove Exchange_rate items
				$db->execute();

				// Remove Exchange_rate items from the ucm history table
				$exchange_rate_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $exchange_rate_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($exchange_rate_condition);
				$db->setQuery($query);
				// Execute the query to remove Exchange_rate items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Ip_table alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.ip_table') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$ip_table_found = $db->getNumRows();
		// Now check if there were any rows
		if ($ip_table_found)
		{
			// Since there are load the needed  ip_table type ids
			$ip_table_ids = $db->loadColumn();
			// Remove Ip_table from the content type table
			$ip_table_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.ip_table') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($ip_table_condition);
			$db->setQuery($query);
			// Execute the query to remove Ip_table items
			$ip_table_done = $db->execute();
			if ($ip_table_done);
			{
				// If succesfully remove Ip_table add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.ip_table) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Ip_table items from the contentitem tag map table
			$ip_table_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_locationdata.ip_table') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($ip_table_condition);
			$db->setQuery($query);
			// Execute the query to remove Ip_table items
			$ip_table_done = $db->execute();
			if ($ip_table_done);
			{
				// If succesfully remove Ip_table add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.ip_table) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Ip_table items from the ucm content table
			$ip_table_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_locationdata.ip_table') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($ip_table_condition);
			$db->setQuery($query);
			// Execute the query to remove Ip_table items
			$ip_table_done = $db->execute();
			if ($ip_table_done);
			{
				// If succesfully remove Ip_table add queued success message.
				$app->enqueueMessage(JText::_('The (com_locationdata.ip_table) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Ip_table items are cleared from DB
			foreach ($ip_table_ids as $ip_table_id)
			{
				// Remove Ip_table items from the ucm base table
				$ip_table_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $ip_table_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($ip_table_condition);
				$db->setQuery($query);
				// Execute the query to remove Ip_table items
				$db->execute();

				// Remove Ip_table items from the ucm history table
				$ip_table_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $ip_table_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($ip_table_condition);
				$db->setQuery($query);
				// Execute the query to remove Ip_table items
				$db->execute();
			}
		}

		// If All related items was removed queued success message.
		$app->enqueueMessage(JText::_('All related items was removed from the <b>#__ucm_base</b> table'));
		$app->enqueueMessage(JText::_('All related items was removed from the <b>#__ucm_history</b> table'));

		// Remove locationdata assets from the assets table
		$locationdata_condition = array( $db->quoteName('name') . ' LIKE ' . $db->quote('com_locationdata%') );

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__assets'));
		$query->where($locationdata_condition);
		$db->setQuery($query);
		$ip_table_done = $db->execute();
		if ($ip_table_done);
		{
			// If succesfully remove locationdata add queued success message.
			$app->enqueueMessage(JText::_('All related items was removed from the <b>#__assets</b> table'));
		}

		// little notice as after service, in case of bad experience with component.
		echo '<h2>Did something go wrong? Are you disappointed?</h2>
		<p>Please let me know at <a href="mailto:joomla@vdm.io">joomla@vdm.io</a>.
		<br />We at Vast Development Method are committed to building extensions that performs proficiently! You can help us, really!
		<br />Send me your thoughts on improvements that is needed, trust me, I will be very grateful!
		<br />Visit us at <a href="https://www.vdm.io/" target="_blank">https://www.vdm.io/</a> today!</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// get application
		$app = JFactory::getApplication();
		// is redundant ...hmmm
		if ($type == 'uninstall')
		{
			return true;
		}
		// the default for both install and update
		$jversion = new JVersion();
		if (!$jversion->isCompatible('3.6.0'))
		{
			$app->enqueueMessage('Please upgrade to at least Joomla! 3.6.0 before continuing!', 'error');
			return false;
		}
		// do any updates needed
		if ($type == 'update')
		{
		}
		// do any install needed
		if ($type == 'install')
		{
		}
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		// set the default component settings
		if ($type == 'install')
		{

			// Get The Database object
			$db = JFactory::getDbo();

			// Create the country content type object.
			$country = new stdClass();
			$country->type_title = 'Locationdata Country';
			$country->type_alias = 'com_locationdata.country';
			$country->table = '{"special": {"dbtable": "#__locationdata_country","key": "id","type": "Country","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$country->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","currency":"currency","worldzone":"worldzone","codethree":"codethree","codetwo":"codetwo","alias":"alias"}}';
			$country->router = 'LocationdataHelperRoute::getCountryRoute';
			$country->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/country.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "currency","targetTable": "#__locationdata_currency","targetColumn": "codethree","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$country_Inserted = $db->insertObject('#__content_types', $country);

			// Create the currency content type object.
			$currency = new stdClass();
			$currency->type_title = 'Locationdata Currency';
			$currency->type_alias = 'com_locationdata.currency';
			$currency->table = '{"special": {"dbtable": "#__locationdata_currency","key": "id","type": "Currency","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$currency->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","codethree":"codethree","numericcode":"numericcode","symbol":"symbol","positivestyle":"positivestyle","decimalsymbol":"decimalsymbol","thousands":"thousands","negativestyle":"negativestyle","decimalplace":"decimalplace","alias":"alias"}}';
			$currency->router = 'LocationdataHelperRoute::getCurrencyRoute';
			$currency->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/currency.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","numericcode","decimalplace"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$currency_Inserted = $db->insertObject('#__content_types', $currency);

			// Create the exchange_rate content type object.
			$exchange_rate = new stdClass();
			$exchange_rate->type_title = 'Locationdata Exchange_rate';
			$exchange_rate->type_alias = 'com_locationdata.exchange_rate';
			$exchange_rate->table = '{"special": {"dbtable": "#__locationdata_exchange_rate","key": "id","type": "Exchange_rate","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$exchange_rate->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","date_rate":"date_rate","from":"from","to":"to","rate":"rate","alias":"alias","ask":"ask","bid":"bid"}}';
			$exchange_rate->router = 'LocationdataHelperRoute::getExchange_rateRoute';
			$exchange_rate->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/exchange_rate.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "from","targetTable": "#__locationdata_currency","targetColumn": "codethree","displayColumn": "name"},{"sourceColumn": "to","targetTable": "#__locationdata_currency","targetColumn": "codethree","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$exchange_rate_Inserted = $db->insertObject('#__content_types', $exchange_rate);

			// Create the ip_table content type object.
			$ip_table = new stdClass();
			$ip_table->type_title = 'Locationdata Ip_table';
			$ip_table->type_alias = 'com_locationdata.ip_table';
			$ip_table->table = '{"special": {"dbtable": "#__locationdata_ip_table","key": "id","type": "Ip_table","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$ip_table->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "registry","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"ip_from":"ip_from","ip_to":"ip_to","protocol":"protocol","registry":"registry","cntry":"cntry","assigned":"assigned","ctry":"ctry"}}';
			$ip_table->router = 'LocationdataHelperRoute::getIp_tableRoute';
			$ip_table->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/ip_table.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","protocol","assigned"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "cntry","targetTable": "#__locationdata_country","targetColumn": "codethree","displayColumn": "codethree"},{"sourceColumn": "ctry","targetTable": "#__locationdata_country","targetColumn": "codetwo","displayColumn": "codetwo"}]}';

			// Set the object into the content types table.
			$ip_table_Inserted = $db->insertObject('#__content_types', $ip_table);


			// Install the global extenstion params.
			$query = $db->getQuery(true);
			// Field to update.
			$fields = array(
				$db->quoteName('params') . ' = ' . $db->quote('{"autorName":"Llewellyn van der Merwe","autorEmail":"joomla@vdm.io","local_encryption":"localKey34fdsEkl","country":"USA","timer_exchange_rate":"-5 hours","timer_ip_table":"-1 day","use_proxy":"true","check_in":"-1 day","save_history":"1","history_limit":"10","uikit_load":"1","uikit_min":"","uikit_style":""}'),
			);
			// Condition.
			$conditions = array(
				$db->quoteName('element') . ' = ' . $db->quote('com_locationdata')
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$allDone = $db->execute();

			echo '<a target="_blank" href="https://www.vdm.io/" title="Location Data">
				<img src="components/com_locationdata/assets/images/vdm-component.jpg"/>
				</a>';
		}
		// do any updates needed
		if ($type == 'update')
		{

			// Get The Database object
			$db = JFactory::getDbo();

			// Create the country content type object.
			$country = new stdClass();
			$country->type_title = 'Locationdata Country';
			$country->type_alias = 'com_locationdata.country';
			$country->table = '{"special": {"dbtable": "#__locationdata_country","key": "id","type": "Country","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$country->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","currency":"currency","worldzone":"worldzone","codethree":"codethree","codetwo":"codetwo","alias":"alias"}}';
			$country->router = 'LocationdataHelperRoute::getCountryRoute';
			$country->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/country.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "currency","targetTable": "#__locationdata_currency","targetColumn": "codethree","displayColumn": "name"}]}';

			// Check if country type is already in content_type DB.
			$country_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($country->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$country->type_id = $db->loadResult();
				$country_Updated = $db->updateObject('#__content_types', $country, 'type_id');
			}
			else
			{
				$country_Inserted = $db->insertObject('#__content_types', $country);
			}

			// Create the currency content type object.
			$currency = new stdClass();
			$currency->type_title = 'Locationdata Currency';
			$currency->type_alias = 'com_locationdata.currency';
			$currency->table = '{"special": {"dbtable": "#__locationdata_currency","key": "id","type": "Currency","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$currency->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","codethree":"codethree","numericcode":"numericcode","symbol":"symbol","positivestyle":"positivestyle","decimalsymbol":"decimalsymbol","thousands":"thousands","negativestyle":"negativestyle","decimalplace":"decimalplace","alias":"alias"}}';
			$currency->router = 'LocationdataHelperRoute::getCurrencyRoute';
			$currency->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/currency.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","numericcode","decimalplace"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"}]}';

			// Check if currency type is already in content_type DB.
			$currency_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($currency->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$currency->type_id = $db->loadResult();
				$currency_Updated = $db->updateObject('#__content_types', $currency, 'type_id');
			}
			else
			{
				$currency_Inserted = $db->insertObject('#__content_types', $currency);
			}

			// Create the exchange_rate content type object.
			$exchange_rate = new stdClass();
			$exchange_rate->type_title = 'Locationdata Exchange_rate';
			$exchange_rate->type_alias = 'com_locationdata.exchange_rate';
			$exchange_rate->table = '{"special": {"dbtable": "#__locationdata_exchange_rate","key": "id","type": "Exchange_rate","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$exchange_rate->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "name","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"name":"name","date_rate":"date_rate","from":"from","to":"to","rate":"rate","alias":"alias","ask":"ask","bid":"bid"}}';
			$exchange_rate->router = 'LocationdataHelperRoute::getExchange_rateRoute';
			$exchange_rate->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/exchange_rate.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "from","targetTable": "#__locationdata_currency","targetColumn": "codethree","displayColumn": "name"},{"sourceColumn": "to","targetTable": "#__locationdata_currency","targetColumn": "codethree","displayColumn": "name"}]}';

			// Check if exchange_rate type is already in content_type DB.
			$exchange_rate_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($exchange_rate->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$exchange_rate->type_id = $db->loadResult();
				$exchange_rate_Updated = $db->updateObject('#__content_types', $exchange_rate, 'type_id');
			}
			else
			{
				$exchange_rate_Inserted = $db->insertObject('#__content_types', $exchange_rate);
			}

			// Create the ip_table content type object.
			$ip_table = new stdClass();
			$ip_table->type_title = 'Locationdata Ip_table';
			$ip_table->type_alias = 'com_locationdata.ip_table';
			$ip_table->table = '{"special": {"dbtable": "#__locationdata_ip_table","key": "id","type": "Ip_table","prefix": "locationdataTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$ip_table->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "registry","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "null","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"ip_from":"ip_from","ip_to":"ip_to","protocol":"protocol","registry":"registry","cntry":"cntry","assigned":"assigned","ctry":"ctry"}}';
			$ip_table->router = 'LocationdataHelperRoute::getIp_tableRoute';
			$ip_table->content_history_options = '{"formFile": "administrator/components/com_locationdata/models/forms/ip_table.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","protocol","assigned"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "cntry","targetTable": "#__locationdata_country","targetColumn": "codethree","displayColumn": "codethree"},{"sourceColumn": "ctry","targetTable": "#__locationdata_country","targetColumn": "codetwo","displayColumn": "codetwo"}]}';

			// Check if ip_table type is already in content_type DB.
			$ip_table_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($ip_table->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$ip_table->type_id = $db->loadResult();
				$ip_table_Updated = $db->updateObject('#__content_types', $ip_table, 'type_id');
			}
			else
			{
				$ip_table_Inserted = $db->insertObject('#__content_types', $ip_table);
			}


			echo '<a target="_blank" href="https://www.vdm.io/" title="Location Data">
				<img src="components/com_locationdata/assets/images/vdm-component.jpg"/>
				</a>
				<h3>Upgrade to Version 1.0.1 Was Successful! Let us know if anything is not working as expected.</h3>';
		}
	}
}
