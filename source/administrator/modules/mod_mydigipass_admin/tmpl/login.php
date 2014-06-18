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

// Get input cookie object
$inputCookie  = JFactory::getApplication()->input->cookie;
$inputCookie->set('digipassadmin', 1, time() + 360, "/");
$inputCookie->set('digipasssession', JFactory::getSession()->getId(), time() + 360, "/");
?>
<a data-text="secure-login" data-style="large" class="dpplus-connect" data-origin="<?php echo $mdp_base_uri ?>"
   data-client-id="<?php echo $client_id ?>" data-redirect-uri="<?php echo $redirect_uri; ?>"
   title="MYDIGIPASS.COM Secure Login" href="#">Secure Login with MYDIGIPASS.COM</a>

<script type="text/javascript" src="https://static.mydigipass.com/dp_connect.js"></script>