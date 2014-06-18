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
class JFormFieldMydigipassbutton extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5.5
	 */
	protected $type = 'Clicks';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$html = array();
		$user = JFactory::getUser();

		// We need the module params
		$module = &JModuleHelper::getModule('mydigipass');
		$moduleParams = new JRegistry($module->params);

		$client_id = $moduleParams->get("clientid", "");
		$redirect_uri = $moduleParams->get("redirecturi", "");
		$mdp_base_uri = ($moduleParams->get("sandbox", 0)) ? "https://sandbox.mydigipass.com" : "https://mydigipass.com";

		if ($user->id == 0)
		{
			$html[] = '<a data-text="secure-login" data-style="large" class="dpplus-connect" data-origin="' . $mdp_base_uri . '"
			   data-client-id="' . $client_id . '" data-redirect-uri="' . $redirect_uri  . '"
			   title="MYDIGIPASS.COM Secure Login" href="#">Secure Login with MYDIGIPASS.COM</a>';

			$html[] = '<script type="text/javascript" src="https://static.mydigipass.com/dp_connect.js"></script>';

			return implode("", $html);
		}
		else
		{
			return "";
		}
	}

	/**
	 * Returns an empty label string
	 *
	 * @return  string
	 */
	protected function getLabel()
	{
		return "";
	}
}
