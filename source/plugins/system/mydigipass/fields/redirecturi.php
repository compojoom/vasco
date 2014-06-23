<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

$ret = JFormHelper::loadFieldClass('clicks');

/**
 * Class JFormFieldMydigipassbutton
 *
 * @since  1.0.0
 */
class JFormFieldRedirecturi extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5.5
	 */
	protected $type = 'text';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$value = Juri::root() . 'index.php?mydigipass=1';

		return $value . '<input type="hidden" name="' . $this->name . '" value="' . $value . '" />';
	}
}
