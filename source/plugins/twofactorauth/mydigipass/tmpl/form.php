<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="well">
	<?php echo JText::_('PLG_TWOFACTORAUTH_VASCO_INTRO') ?>
</div>

<?php if ($new_totp): ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_VASCO_STEP1_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_VASCO_STEP1_TEXT') ?>
	</p>

	<a class="dpplus-connect" data-origin="<?php echo $mdp_base_uri; ?>"  data-client-id="<?php echo $client_id ?>"
	   data-redirect-uri="<?php echo $redirect_uri ?>" href="#">Mydigipass.com Secure Login</a>

	<script type="text/javascript" src="https://static.mydigipass.com/dp_connect.js"></script>
</fieldset>
<?php else: ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_RESET_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_RESET_TEXT') ?>
	</p>
</fieldset>
<?php endif; ?>
