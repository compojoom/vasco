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
?>
<!-- START Mydigipass Admin module by compojoom.com  -->
<div class="mydigipass<?php echo $moduleclass_sfx ?>">
	<?php
	if ($user->id == 0)
	{
		// Show login button
		require JModuleHelper::getLayoutPath('mod_mydigipass_admin', 'login');
	}
	?>
</div>
<!-- END Mydigipass Admin module by compojoom.com  -->