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
class PlgSystemMydigipass extends JPlugin
{
	/**
	 * Checks if connect / login token is there, if yes -> Connects user to mydigipass
	 *
	 * @return  boolean
	 */
	public function onAfterInitialise()
	{
		$input = JFactory::getApplication()->input;

		// Check if mydigipass get parameter is set
		if (!$input->get('mydigipass', 0))
		{
			return true;
		}

		$code = $input->get('code', 0);

		// Check if the oauth code is supplied
		if (!$code)
		{
			return true;
		}

		// Check if we connect or login
		$user = JFactory::getUser();

		$inputCookie = JFactory::getApplication()->input->cookie;
		$isAdmin     = $inputCookie->get("digipassadmin", 0);

		// Fix
		$mdp_base_uri  = ($this->params->get("sandbox", 0)) ? "https://sandbox.mydigipass.com" : "https://mydigipass.com";
		$client_id     = $this->params->get("clientid", "");
		$client_secret = $this->params->get("clientsecret", "");
		$redirect_uri  = $this->params->get("redirecturi", "");

		// Check if should connect - on admin we always login, even if the user is logged into the frontend
		if ($user->id > 0 && !$isAdmin)
		{
			// Connect
			$session = JFactory::getSession();

			// configuration of service
			$session->set('base_uri', $mdp_base_uri);
			$session->set('client_id', $client_id);
			$session->set('client_secret', $client_secret);

			$options = new JRegistry();

			$options->set("redirecturi", $redirect_uri);
			$options->set("clientid", $client_id);
			$options->set("clientsecret", $client_secret);
			$options->set("tokenurl", $mdp_base_uri . "/oauth/token");

			$service = new JOAuth2Client($options);

			try
			{
				$return = $service->authenticate();

				if (empty($return))
				{
					throw new RuntimeException("Error connecting profile");
				}

				$uuid = $return["uuid"];

				// Save the uuid in the profile database
				$db    = JFactory::getDbo();
				$query = "INSERT INTO #__user_profiles VALUES(
								" . $db->quote($user->id) . ",
								" . $db->quote("uuid") . ",
								" . $db->quote($uuid) . ",
								0
							)";

				$db->setQuery($query);
				$db->execute();

				if ($db->getErrorNum())
				{
					throw new Exception("Error saving uuid " . $db->getErrorMsg());
				}
			}
			catch (Exception $e)
			{
				echo 'Exception caught: ' . $e->getMessage();
			}
		}
		else
		{
			try
			{
				// Login request
				$options = new JRegistry();

				$options->set("redirecturi", $redirect_uri);
				$options->set("clientid", $client_id);
				$options->set("clientsecret", $client_secret);
				$options->set("tokenurl", $mdp_base_uri . "/oauth/token");

				$service = new JOAuth2Client($options);
				$return = $service->authenticate();

				$uuid = $return["uuid"];

				if (empty($return))
				{
					throw new RuntimeException("Error loging in");
				}

				// Let's check if the user is already connected
				$db = JFactory::getDbo();

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

				if (!$isAdmin)
				{
					// Login User
					// Get the global JAuthentication object.
					jimport('joomla.user.authentication');

					// We need that for the auth response to work! (Don't delete)
					$authenticate = JAuthentication::getInstance();

					// Get plugins
					$plugins = JPluginHelper::getPlugin('authentication');

					// Create authentication response
					$response = new JAuthenticationResponse;

					$response->username = $user->username;
					$response->fullname = $user->name;
					$response->password = $user->password;
					$response->status   = JAuthentication::STATUS_SUCCESS;

					$opt = array();

					$opt['action'] = "core.login";

					if ($isAdmin)
					{
						$opt['action']   = "core.login.admin";
						$opt['clientid'] = 1;
					}

					// Import the user plugin group.
					JPluginHelper::importPlugin('user');
					$dispatcher = JEventDispatcher::getInstance();

					// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
					$results = $dispatcher->trigger('onUserLogin', array((array) $response, $opt));

					$user = JFactory::getUser();

					$user->set('cookieLogin', true);

					$opt['user']         = $user;
					$opt['responseType'] = $response->type;

					// The user is successfully logged in. Run the after login events
					$dispatcher->trigger('onUserAfterLogin', array($opt));

				}
				else
				{
					// Redirect to backend
					// Generate token for security
					$token = JSession::getFormToken(true);

					// Save the token in the profile database
					$db    = JFactory::getDbo();
					$query = "INSERT INTO #__user_profiles VALUES(
								" . $db->quote($user->id) . ",
								" . $db->quote("token") . ",
								" . $db->quote($token) . ",
								0
							)";

					$db->setQuery($query);
					$db->execute();

					// TODO Exception handling..
					if ($db->getErrorNum())
					{
						throw new Exception("Error saving token " . $db->getErrorMsg());
					}

					$inputCookie->set('digitoken', $token, time() + 120, "/");

					return JFactory::getApplication()->redirect(JUri::base() . "administrator/index.php?digiadmin=1&uuid=" . $uuid);
				}
			}
			catch (Exception $e)
			{
				echo 'Exception caught: ' . $e->getMessage();
			}
		}
	}

	/**
	 * Extends the login form with our button
	 *
	 * @param   JForm   $form - The form
	 * @param   JObject $data - The date
	 *
	 * @return  boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		if ($form->getName() != "com_users.login")
		{
			return true;
		}

		// Add the field
		JFormHelper::addFieldPath(dirname(__FILE__) . '/fields');

		// Add the path to our form for overriding
		JForm::addFormPath(dirname(__FILE__) . '/forms');

		$form->loadFile('mydigipass', true);

		return true;
	}
}
