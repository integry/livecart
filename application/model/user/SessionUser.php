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
	/**
	 * Get current user (from session)

	 * @return User
	 */
	public static function getUser()
	{
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

		if ($app->getSessionHandler())
		{
			$app->getSessionHandler()->setUser($user);
		}

		return $user;
	}

	public static function setUser(User $user)
	{
		$app = ActiveRecordModel::getApplication();

		$app->processRuntimePlugins('session/before-login');

		$session = new Session();
		$session->set('User', $user->getID());
		$session->set('UserGroup', $user->userGroup->get() ? $user->userGroup->get()->getID() : 0);

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
