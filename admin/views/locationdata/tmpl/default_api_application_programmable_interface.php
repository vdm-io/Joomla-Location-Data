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
	@subpackage		default_api_application_programmable_interface.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html 
	
	You can via an url receive all the data related to any given IP address in a Json object. 
                                                             
/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 

?>
<div class="well well-small">
<h2 class="nav-header">How does the API Work?</h2>
<p>You can via an url receive all the data related to any given IP address in a Json object.</p>
<p>Click the following link as demostration: http://test.vdm/index.php?option=com_ipdata&task=api.data&format=json&ip=105.232.113.65&key=0&raw=1&s=0&m=0&base=EUR</p>
</div>
<div class="well well-small">
	<h2 class="nav-header">The URL is build up from several segments:</h2>
	<ol>
		<li>Your Domain =&gt; [ http://test.vdm/ ]<br>Is fixed and must always be in the API call</li>
		<li>Applcation Defaults =&gt; [ index.php?option=com_ipdata&amp;task=api.data&amp;format=json ]<br>Is fixed and must always be in the API call</li>
		<li>IP Address =&gt; [ &amp;ip=105.232.113.65 ]<br>Any given IP, don't remove the dots!</li>
		<li>API Key =&gt; [ &amp;key=0 ]<br>The API key is build with the following formats:<br>
			<ul>
				<li><code>md5([PrivateKey])</code></li>
				<li><code>md5([username]_[PrivateKey])</code></li>
			</ul>
			One example will be <code>$key = md5('james_0101010101010');</code> and then <code>echo $key;</code> will then render: <b>1dd8d61b2f27e7993e5421ebbf059753</b> in PHP. If the Access level is set to public no API key is needed. <br>To change the API key options <a href="###">click here</a>
		</li>
		<li>Return Wrapper =&gt; [ &amp;raw=1 ] (optional)<br>The wrapper has only two options 1 = raw and 0 = wrapped in brakets</li>
		<li>Return Mode =&gt; [ &amp;m=0 ] (optional)<br>The mode is used to return only a selected set of data, these modes are as follow:<br>
			<ul>
				<li>0 =&gt; ALL</li>
				<li>1 =&gt; IP_STR</li>
				<li>2 =&gt; IP_VALUE</li>
				<li>3 =&gt; IP_RANGE_NUMERICAL</li>
				<li>4 =&gt; IP_RANGE</li>
				<li>5 =&gt; IP_REGISTRY</li>
				<li>6 =&gt; IP_ASSIGNED_UNIXTIME</li>
				<li>7 =&gt; COUNTRY_ALL</li>
				<li>8 =&gt; COUNTRY_NAME</li>
				<li>9 =&gt; COUNTRY_CODE_TWO</li>
				<li>10 =&gt; COUNTRY_CODE_THREE</li>
				<li>11 =&gt; CURRENCY_ALL</li>
				<li>12 =&gt; CURRENCY_NAME</li>
				<li>13 =&gt; CURRENCY_CODE_THREE</li>
				<li>14 =&gt; CURRENCY_CODE_NUMERIC</li>
				<li>15 =&gt; CURRENCY_SYMBOL</li>
				<li>16 =&gt; CURRENCY_DECIMAL_PLACE</li>
				<li>17 =&gt; CURRENCY_DECIMAL_SYMBOL</li>
				<li>18 =&gt; CURRENCY_POSITIVE_STYLE</li>
				<li>19 =&gt; CURRENCY_NEGATIVE_STYLE</li>
				<li>20 =&gt; EXCHANGE_RATE_ALL</li>
				<li>21 =&gt; EXCHANGE_RATE_ID</li>
				<li>22 =&gt; EXCHANGE_RATE_NAME</li>
				<li>23 =&gt; EXCHANGE_RATE</li>
				<li>24 =&gt; EXCHANGE_RATE_ASK</li>
				<li>25 =&gt; EXCHANGE_RATE_BID</li>
				<li>26 =&gt; EXCHANGE_RATE_DATE</li>
			</ul>
		</li>
		<li>String Only =&gt; [ &amp;s=0 ] (optional)<br>Setting the operator value to 1 when the return result is only one value then a string will be returned and not an object. So when you use for example mode 8 that returns only one result value the 'COUNTRY_NAME' you can use this 'String Only' option to return only the name of the country as a string. Setting the operator value to 0 or having no operator value, will return the result in an object. Please note, when more than one result value is returned the operator loses precedence, so the result will always be an Oject.
		</li>
		<li>Base Currency =&gt; [ &amp;base=EUR ] (optional)<br>If not set the base currency will default to the <b>component default currency</b>. To change the default currency <a href="###">click here</a>
		</li>
	</ol>
</div>
