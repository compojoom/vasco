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

// Module class
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$user = JFactory::getUser();

JFactory::getSession()->set("digiadmin", 0);
?>
<!-- START Mydigipass module by compojoom.com  -->
<div class="mydigipass<?php echo $moduleclass_sfx ?>">
	<?php
	$inputCookie  = JFactory::getApplication()->input->cookie;
	$value = $inputCookie->get("digipassadmin", 0);

	var_dump($value);

	if ($user->id == 0)
	{
		// Show login button
		require JModuleHelper::getLayoutPath('mod_mydigipass', 'login');
	}
	else
	{
		// Let's check if the user is already connected
		$db = JFactory::getDbo();

		$db->setQuery(
			'SELECT profile_key, profile_value FROM #__user_profiles' .
			' WHERE user_id = '.(int) $user->id .
			' AND profile_key = ' . $db->quote("uuid").
			' ORDER BY ordering'
		);

		$result = $db->loadObject();

		if (empty($result))
		{
			// Show connect button only if not connected yet
			require JModuleHelper::getLayoutPath('mod_mydigipass', 'connect');
		}
		else
		{
			echo JText::_("MOD_MYDIGIPASS_YOU_ARE_ALREADY_CONNECTED");
		}
	}
	?>
</div>
<!-- END Matukio Upcoming module by compojoom.com  -->