<?php

namespace user;

/**
 * User session handler (set user as logged in / logout)
 *
 * @package application/model/user
 * @author Integry Systems <http://integry.com>
 */
class SessionUser extends \Phalcon\DI\Injectable
{
	private static $currentUser;

	public function __construct(\Phalcon\DI\FactoryDefault $di)
	{
		$this->setDI($di);
	}

	/**
	 * Get current user (from session)

	 * @return User
	 */
	public function getUser()
	{
		if (!empty(self::$currentUser))
		{
			return self::$currentUser;
		}

		$session = $this->session;

		$id = $session->get('User');
		$app = $this->application;

		if (!$id)
		{
			$user = self::getAnonymousUser();
		}
		else
		{
			$user = User::getInstanceById($id);

			// set user's prefered locale code
			$reqLang = $this->request->get('requestLanguage');
			$localeCode = $reqLang ? $reqLang : $app->getLocaleCode();

			try
			{
				if ($session->get('userLocale') != $localeCode)
				{
					$user->locale = $localeCode;
					$user->save();

					$session->set('userLocale', $localeCode);
				}

				if (!$session->has('UserGroup') || is_null($session->get('UserGroup')))
				{
					/*
					$group = $user->userGroup ? $user->userGroup->getID() : 0;
					$session->set('UserGroup', $group);
					*/
				}

				//$user->userGroup->set(UserGroup::getInstanceByID($session->get('UserGroup')));
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

	public function setUser(User $user)
	{
		self::$currentUser = $user;

		$app = $this->getDI()->get('application');

		$app->processRuntimePlugins('session/before-login');

		$session = $this->getDI()->get('session');
		$session->set('User', $user->getID());

		//$session->set('UserGroup', $user->userGroup ? $user->userGroup->getID() : 0);

		if ($app->getSessionHandler())
		{
			$app->getSessionHandler()->setUser($user);
		}

		$app->processRuntimePlugins('session/login');
	}

	public function destroy()
	{
		$app = $this->getDI()->get('application');
		$app->processRuntimePlugins('session/before-logout');

		$session = $this->getDI()->get('session');
		$session->remove('User');
		$session->remove('CustomerOrder');

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
			$instance = new User;
			$instance->setID(User::ANONYMOUS_USER_ID);
		}

		return $instance;
	}
}

?>
