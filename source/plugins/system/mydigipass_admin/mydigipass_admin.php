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

/**
 * Class PlgSystemMydigipass
 *
 * @since 1.0
 */
class PlgSystemMydigipass_Admin extends JPlugin
{
	/**
	 * Logs the user into the backend
	 *
	 * @throws  Exception on Login error
	 * @return  boolean
	 */
	public function onAfterInitialise()
	{


		$input = JFactory::getApplication()->input;

		// Check if mydigipass get parameter is set
		if (!$input->getInt('digiadmin', 0))
		{
			return true;
		}


		$uuid = $input->get('uuid', 0);

		// Check if uuid is not empty
		if (!$uuid)
		{
			return true;
		}

		$user = JFactory::getUser();

		// We don't do anything if we are already logged in the backend
		if ($user->id)
		{
			return true;
		}

		$inputCookie  = JFactory::getApplication()->input->cookie;
		$isAdmin = $inputCookie->get("digipassadmin", 0);
		$digisession = $inputCookie->get("digipasssession", 0);
		$token = $inputCookie->get('digitoken', 0);

		$session = JFactory::getSession();

		// Check session
		if ($session->getId() != $digisession)
		{
			return true;
		}

		// Check token
		if (empty($token))
		{
			return true;
		}

		// Let's check if the token matches the saved one in the datase
		$db = JFactory::getDbo();

		$db->setQuery(
			'SELECT * FROM #__user_profiles WHERE profile_value = ' . $db->quote($token)
		);

		$result = $db->loadObject();

		if (empty($result))
		{
			throw new Exception('The security token does not match!');
		}

		// Delete the token (it should just be used once!)
		$db->setQuery("DELETE FROM #__user_profiles WHERE profile_value = " . $db->quote($token));
		$db->execute();

		// Unset cookies
		$inputCookie->set('digipassadmin', "", time() -1, "/");
		$inputCookie->set('digipasssession', "", time() -1, "/");
		$inputCookie->set('digitoken', "", time() -1, "/");

		// Let's check if the user is already connected
		$db->setQuery(
			'SELECT * FROM #__user_profiles WHERE profile_value = ' . $db->quote($uuid)
		);

		$result = $db->loadObject();

		if (empty($result))
		{
			// Show connect button only if not connected yet
			throw new Exception('You are not connected with this Joomla site!');
		}

		$user = JFactory::getUser($result->user_id);

		if (empty($user) || $user->block)
		{
			throw new Exception('Your user account was not found!');
		}

		// Login User
		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		$authenticate = JAuthentication::getInstance();

		// Get plugins
		$plugins = JPluginHelper::getPlugin('authentication');

		// Create authentication response
		$response = new JAuthenticationResponse;

		$response->username = $user->username;
		$response->fullname = $user->name;
		$response->password = $user->password;
		$response->status = JAuthentication::STATUS_SUCCESS;

		$opt = array();

		$opt['action'] = "core.login.admin";

		// Import the user plugin group.
		JPluginHelper::importPlugin('user');
		$dispatcher = JEventDispatcher::getInstance();

		// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
		$results = $dispatcher->trigger('onUserLogin', array((array) $response, $opt));

		$user = JFactory::getUser();

		$user->set('cookieLogin', true);

		$opt['user'] = $user;
		$opt['responseType'] = $response->type;

		// The user is successfully logged in. Run the after login events
		$dispatcher->trigger('onUserAfterLogin', array($opt));

		JFactory::getApplication()->redirect(JUri::base() . "index.php");
	}
}
