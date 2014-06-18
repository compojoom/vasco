<?php
/**
 * @package    plg_twofactorauth_vasco
 * @author     Yves Hoppe <yves@compojoom.com>
 * @date       17.06.14
 *
 * @copyright  Copyright (C) Yves Hoppe - compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once 'helper.php';

/**
 * Class PlgTwofactorauthMydigipass
 *
 * @since 1.0
 */
class PlgTwofactorauthMydigipass extends JPlugin
{
	/**
	 * Method name
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $methodName = 'mydigipass';

	/**
	 * This method returns the identification object for this two factor
	 * authentication plugin.
	 *
	 * @return  stdClass  An object with public properties method and title
	 *
	 * @since   3.2
	 */
	public function onUserTwofactorIdentify()
	{
		$section = (int) $this->params->get('section', 3);

		$current_section = 0;

		try
		{
			$app = JFactory::getApplication();

			if ($app->isAdmin())
			{
				$current_section = 2;
			}
			elseif ($app->isSite())
			{
				$current_section = 1;
			}
		}
		catch (Exception $exc)
		{
			$current_section = 0;
		}

		if (!($current_section & $section))
		{
			return false;
		}

		return (object) array(
			'method' => $this->methodName,
			'title'  => JText::_('PLG_TWOFACTORAUTH_VASCO_METHOD_TITLE')
		);
	}


	/**
	 * Shows the configuration page for this two factor authentication method.
	 *
	 * @param   object   $otpConfig  The two factor auth configuration object
	 * @param   integer  $user_id    The numeric user ID of the user whose form we'll display
	 *
	 * @return  boolean|string  False if the method is not ours, the HTML of the configuration page otherwise
	 *
	 * @see     UsersModelUser::getOtpConfig
	 * @since   3.2
	 */
	public function onUserTwofactorShowConfiguration($otpConfig, $user_id = null)
	{
		if ($otpConfig->method == $this->methodName)
		{
			// This method is already activated. Reuse the same Vascokey ID.
			$vascokey = $otpConfig->config['vascokey'];
		}
		else
		{
			// This methods is not activated yet. We'll need a Vascokey TOTP to setup this Vascokey.
			$vascokey = '';
		}

		// Is this a new TOTP setup? If so, we'll have to show the code validation field.
		$new_totp = $otpConfig->method != $this->methodName;

		$mdp_base_uri = "https://sandbox.mydigipass.com";
		$client_id = $this->params->get('client_id', '');

		$uri = JURI::current();


		$redirect_uri = "http://localhost/index.php?option=com_users&view=profile"; //. $digipass;

		// Start output buffering
		@ob_start();

		// Include the form.php from a template override. If none is found use the default.
		$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_vasco', true);

		JLoader::import('joomla.filesystem.file');

		if (JFile::exists($path . 'form.php'))
		{
			include_once $path . 'form.php';
		}
		else
		{
			include_once __DIR__ . '/tmpl/form.php';
		}

		// Stop output buffering and get the form contents
		$html = @ob_get_clean();

		// Return the form contents
		return array(
			'method' => $this->methodName,
			'form'   => $html,
		);
	}
}
