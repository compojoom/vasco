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
	public $autoloadLanguage = true;

	/**
	 * Checks if connect / login token is there, if yes -> Connects user to mydigipass
	 *
	 * @return  boolean
	 */
	public function onAfterInitialise()
	{
		$input = JFactory::getApplication()->input;

		try
		{
			// Check if mydigipass get parameter is set
			if ($input->get('mydigipass', 0))
			{
				$this->frontend();
				return true;
			}

			// Check if mydigipass get parameter is set
			if ($input->getInt('digiadmin', 0))
			{
				$this->backend();
				return true;
			}
		}
		catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage($e->getMessage());
			JFactory::getApplication()->redirect(Juri::base());
		}

		return true;

	}

	/**
	 * Handle the backend login logic
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function backend() {
		$input = JFactory::getApplication()->input;
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

		// Let's check if the token matches the saved one in the database
		$result = $this->getProfileRow($token);

		if (empty($result))
		{
			throw new Exception(JText::_('PLG_SYSTEM_MYDIGIPASS_THE_TOKEN_DOESNT_MATCH'));
		}

		$this->deleteToken($token);

		// Unset cookies
		$inputCookie->set('digipassadmin', "", time() -1, "/");
		$inputCookie->set('digipasssession', "", time() -1, "/");
		$inputCookie->set('digitoken', "", time() -1, "/");

		$user = $this->getUser($uuid);

		$this->login($user, array('action' => 'core.login.admin'));

		JFactory::getApplication()->redirect(JUri::base() . "index.php");

		return true;
	}

	/**
	 * Handle the login logic in the frontend.
	 * If user is an admin that tries to login from the backend, redirects to the backend
	 * so that we can log him there
	 *
	 * @return bool
	 * @throws Exception
	 */
	private function frontend() {
		$input = JFactory::getApplication()->input;
		$code = $input->get('code', 0);

		// Check if the oauth code is supplied
		if (!$code)
		{
			return true;
		}

		$user        = JFactory::getUser();
		$inputCookie = JFactory::getApplication()->input->cookie;
		$isAdmin     = $inputCookie->get("digipassadmin", 0);

		// Fix
		$mdp_base_uri  = $this->params->get("sandbox", 0) ? "https://sandbox.mydigipass.com" : "https://mydigipass.com";
		$client_id     = $this->params->get("clientid", "");
		$client_secret = $this->params->get("clientsecret", "");
		$redirect_uri  = $this->params->get("redirecturi", "");

		// Login request
		$options = new JRegistry();
		$options->set("redirecturi", $redirect_uri);
		$options->set("clientid", $client_id);
		$options->set("clientsecret", $client_secret);
		$options->set("tokenurl", $mdp_base_uri . "/oauth/token");
		$service = new JOAuth2Client($options);

		// Check if should connect - on admin we always login, even if the user is logged into the frontend
		try
		{
			if ($user->id > 0 && !$isAdmin)
			{
				// Connect
				$session = JFactory::getSession();

				// configuration of service
				$session->set('base_uri', $mdp_base_uri);
				$session->set('client_id', $client_id);
				$session->set('client_secret', $client_secret);

				$return = $service->authenticate();

				// Save the uuid in the database
				$this->insertIntoProfile($user->id, 'uuid', $return['uuid']);
			}
			else
			{
				$return = $service->authenticate();

				$user = $this->getUser($return["uuid"]);

				if (!$isAdmin)
				{
					$this->login($user, array('action' => 'core.login'));
				}
				else
				{
					// Redirect to backend
					// Generate token for security
					$token = JSession::getFormToken(true);

					// Make sure that we don't have the same token
					$this->deleteToken($token);

					// Save the token in the profile database
					$this->insertIntoProfile($user->id, 'token', $token);

					$inputCookie->set('digitoken', $token, time() + 120, "/");

					JFactory::getApplication()->redirect(JUri::base() . "administrator/index.php?digiadmin=1&uuid=" . $return["uuid"]);
					return true;
				}
			}
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}

		return true;
	}

	/**
	 * Inserts a row in the user_profile table
	 *
	 * @param   int    $id    - the user id
	 * @param   string $key   - the key
	 * @param   string $value - the value to store
	 *
	 * @throws Exception
	 */
	private function insertIntoProfile($id, $key, $value) {
		// Save the row in the profile database
		$db    = JFactory::getDbo();
		$query = "INSERT INTO #__user_profiles VALUES(
								" . $db->quote($id) . ",
								" . $db->quote($key) . ",
								" . $db->quote($value) . ",
								0
							)";

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			throw new Exception(JText::sprintf('PLG_SYSTEM_MYDIGIPASS_ERROR_SAVING', $key , $e->getMessage()));
		}
	}

	/**
	 * Gets a joomla user out of the UUID
	 *
	 * @param   string  $uuid  - Unique user identifier
	 *
	 * @return JUser
	 * @throws Exception
	 */
	private function getUser($uuid) {
		$result = $this->getProfileRow($uuid);

		if (empty($result))
		{
			// Show connect button only if not connected yet
			throw new Exception(JText::_('PLG_SYSTEM_MYDIGIPASS_NOT_CONNECTED_WITH_THE_SITE'));
		}

		$user = JFactory::getUser($result->user_id);

		if (empty($user) || $user->block)
		{
			throw new Exception(JText::_('PLG_SYSTEM_MYDIGIPASS_ACCOUNT_DOESNT_EXIST'));
		}

		return $user;
	}

	/**
	 * Delete a token out of the database
	 *
	 * @param   string  $token  - the security token that we've saved
	 */
	private function deleteToken($token) {
		$db = JFactory::getDbo();
		// Delete the token (it should just be used once!)
		$db->setQuery("DELETE FROM #__user_profiles WHERE profile_value = " . $db->quote($token));
		$db->execute();
	}

	/**
	 * Get a row matchin the profile value
	 *
	 * @param   string  $value  - the value to look for
	 *
	 * @return mixed
	 */
	private function getProfileRow($value) {
		// Let's check if the user is already connected
		$db = JFactory::getDbo();

		$db->setQuery(
			'SELECT * FROM #__user_profiles WHERE profile_value = ' . $db->quote($value)
		);

		$result = $db->loadObject();

		return $result;
	}

	private function login($user, $opt = array()){
		// Login User
		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		JAuthentication::getInstance();

		// Get plugins
		JPluginHelper::getPlugin('authentication');

		// Create authentication response
		$response = new JAuthenticationResponse;

		$response->username = $user->username;
		$response->fullname = $user->name;
		$response->password = $user->password;
		$response->status = JAuthentication::STATUS_SUCCESS;

		$response->type = 'mydigipass';

		// Import the user plugin group.
		JPluginHelper::importPlugin('user');
		$dispatcher = JEventDispatcher::getInstance();

		// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
		$dispatcher->trigger('onUserLogin', array((array) $response, $opt));

		$user = JFactory::getUser();

		$user->set('cookieLogin', true);

		$opt['user'] = $user;
		$opt['responseType'] = $response->type;

		// The user is successfully logged in. Run the after login events
		$dispatcher->trigger('onUserAfterLogin', array($opt));
	}

	/**
	 * We'll use this event to block the user from login in with the standard login form
	 * if he has already connected his mydigipass.com account
	 *
	 * @param array $response  - the response object
	 * @param array $opt       - any options
	 *
	 * @throws Exception
	 */
	public function onUserLogin(array $response, $opt)
	{
		// are we dealing wtih a mydigipass login? If not, let us find out if the user has connected his account
		if ($response['type'] != 'mydigipass' && $this->params->get('block_login', 0))
		{
			$user = $this->getJoomlaUser($response);

			if ($user->id)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('profile_value')
					->from('#__user_profiles')
					->where($db->qn('profile_key') . '=' . $db->q('uuid'))
					->where($db->qn('user_id') . '=' . $db->q($user->id));
				$db->setQuery($query);

				$uuid = $db->loadObject();

				if ($uuid)
				{

					$appl = JFactory::getApplication();

					// Funny enough - if we don't redirect after we enqueue the message, no message is shown
					// Most probably because of the $session->close() later on...
					$appl->enqueueMessage(JText::_('PLG_SYSTEM_MYDIGIPASS_CONNECTED_WITH_MYDIGIPASS_USE_MYDIGIPASS_LOGIN'));
					$appl->redirect(Juri::current());

					// Log the user out
					$appl->logout($user->id);

					// Close the session
					$session = JFactory::getSession();
					$session->close();
				}
			}
		}
	}

	/**
	 * Load a joomla user object
	 *
	 * @param   array  $user  - info abot the user to load
	 *
	 * @return JUser
	 */
	private function getJoomlaUser($user)
	{
		JLoader::import('joomla.user.helper');
		$instance = new JUser();

		if ($id = intval(JUserHelper::getUserId($user['username'])))
		{
			$instance->load($id);

			return $instance;
		}

		return $instance;
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
