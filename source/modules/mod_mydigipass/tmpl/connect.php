<?php
/**
 * @package    mod_mydigipass
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       17.06.14
 *
 * @copyright  Copyright (C) Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

JHTML::_('stylesheet', 'media/mod_mydigipass/css/basic.css');

$plugin = JPluginHelper::getPlugin('system', 'mydigipass');
$plgParams = new JRegistry($plugin->params);

$client_id = $plgParams->get("clientid", "");
$redirect_uri = $plgParams->get("redirecturi", "");
$mdp_base_uri = ($plgParams->get("sandbox", 0)) ? "https://sandbox.mydigipass.com" : "https://mydigipass.com";
?>

<div class="mydigipass<?php echo $moduleclass_sfx ?>">
	<p class="alert alert-info">
		<?php echo JText::_('MOD_MYDIGIPASS_CONNECT_HELP_TEXT'); ?>
	</p>

	<a data-text="connect"
	   data-style="large"
	   class="dpplus-connect"
	   data-origin="<?php echo $mdp_base_uri; ?>"
	   data-client-id="<?php echo $client_id ?>"
	   data-redirect-uri="<?php echo $redirect_uri ?>"
	   title="Connect with MYDIGIPASS.COM"
	   href="#"
	   >Mydigipass.com Secure Connect</a>
	<script type="text/javascript" src="https://static.mydigipass.com/dp_connect.js"></script>


</div>