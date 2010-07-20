<?php

ClassLoader::import('application.model.user.User');

/**
 * User session handler (set user as logged in / logout)
 *
 * @package application.model.user
 * @author Integry Systems <http://integry.com>
 */
class SessionUser
{
	private static $currentUser;
	
	/**
	 * Get current user (from session)

	 * @return User
	 */
	public static function getUser()
	{
		if (!empty(self::$currentUser))
		{
			return self::$currentUser;
		}
	
		$session = new Session();

		$id = $session->get('User');
		$app = ActiveRecordModel::getApplication();

		if (!$id)
		{
			$user = self::getAnonymousUser();
		}
		else
		{
			$user = User::getInstanceById($id);

			// set user's prefered locale code
			$reqLang = $app->getRequest()->get('requestLanguage');
			$localeCode = $reqLang ? $reqLang : $app->getLocaleCode();

			try
			{
				if ($session->get('userLocale') != $localeCode)
				{
					$user->load();
					$user->locale->set($localeCode);
					$user->save();

					$session->set('userLocale', $localeCode);
				}

				if (!$session->isValueSet('UserGroup') || is_null($session->get('UserGroup')))
				{
					$user->load();
					$group = $user->userGroup->get() ? $user->userGroup->get()->getID() : 0;
					$session->set('UserGroup', $group);
				}

				$user->userGroup->set(UserGroup::getInstanceByID($session->get('UserGroup')));
			}
			catch (ARNotFoundException $e)
			{
				$user = self::getAnonymousUser();
			}
		}

		if ($app->getSessionHandler())
		{
			$app->getSessionHandler()->setUser($user);
		}

		return $user;
	}

	public static function setUser(User $user)
	{
		self::$currentUser = $user;
		
		$app = ActiveRecordModel::getApplication();

		$app->processRuntimePlugins('session/before-login');

		$session = new Session();
		$session->set('User', $user->getID());
		$session->set('UserGroup', $user->userGroup->get() ? $user->userGroup->get()->getID() : 0);

		if ($app->getSessionHandler())
		{
			$app->getSessionHandler()->setUser($user);
		}

		$app->processRuntimePlugins('session/login');
	}

	public static function destroy()
	{
		$app = ActiveRecordModel::getApplication();
		$app->processRuntimePlugins('session/before-logout');

		$session = new Session();
		$session->unsetValue('User');
		$session->unsetValue('CustomerOrder');

		$app->processRuntimePlugins('session/logout');
	}

	/**
	 * Get anonymous user

	 * @return User
	 */
	public function getAnonymousUser()
	{
		static $instance;

		if (!$instance)
		{
			$instance = ActiveRecordModel::getNewInstance('User');
			$instance->setID(User::ANONYMOUS_USER_ID);
		}

		return $instance;
	}
}

?>
