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

JHTML::_('script', 'media/mod_mydigipass/js/cookie.js');
JHTML::_('script', 'media/mod_mydigipass/js/visibility.js');

// Get plugin settings
$plugin = JPluginHelper::getPlugin('system', 'mydigipass');
$plgParams = new JRegistry($plugin->params);

$client_id = $plgParams->get("clientid", "");
$redirect_uri = $plgParams->get("redirecturi", "");
$mdp_base_uri = ($plgParams->get("sandbox", 0)) ? "https://sandbox.mydigipass.com" : "https://mydigipass.com";

// Get input cookie object
$inputCookie  = JFactory::getApplication()->input->cookie;

$time = strtotime('now') + 120;
// Set user cookies (because sessions in joomla are divided into frontend and backend sessions!)
$inputCookie->set('digipassadmin', 1, $time, "/");
$inputCookie->set('digipasssession', JFactory::getSession()->getId(), $time, "/");
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		var time = "<?php echo $time; ?>";
		document.addEventListener(visibilityChange, function () {
			docCookies.setItem('digipassadmin', 1, new Date(time*1000), '/');
		}, false);
	});
</script>
<a data-text="secure-login" data-style="large" class="dpplus-connect" data-origin="<?php echo $mdp_base_uri ?>"
   data-client-id="<?php echo $client_id ?>" data-redirect-uri="<?php echo $redirect_uri; ?>"
   title="MYDIGIPASS.COM Secure Login" href="#">Secure Login with MYDIGIPASS.COM</a>

<script type="text/javascript" src="https://static.mydigipass.com/dp_connect.js"></script>