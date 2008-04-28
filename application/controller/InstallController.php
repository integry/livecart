<?php

ClassLoader::import('application.model.system.Installer');
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * LiveCart installer
 *
 * @author Integry Systems
 * @package application.controller
 */
class InstallController extends FrontendController
{
	public function init()
	{
		if ($writeFail = Installer::getUnwritableDirectories())
		{
			echo '<h1>Some directories do not seem to be writable</h1>

				  <p>You\'re probably trying to set up LiveCart now.</p>

				  <p>Before the installation may continue, please make sure that the following directories are writable (chmod to 755 or 777):</p><ul>';

			foreach ($writeFail as $file)
			{
				echo '<li>' . $file . '</li>';
			}

			echo '</ul> <p>Please reload this page when the directory write permissions are fixed. Please <a href="http://support.livecart.com">contact the LiveCart support team</a> if any assistance is required.</p>';

			echo '<p>You can also execute this command from shell to fix the write permissions: <br />
					<div style="margin: 1em; font-family: monospace;">% <span style="font-weight: bold;">chmod -R 0777 ' . implode(' ', $writeFail) . '</span></div></p>';

			exit;
		}

	  	$this->setLayout('install');
	}

	public function index()
	{
		$requirements = Installer::checkRequirements($this->application);
		foreach ($requirements as $req)
		{
			if (1 != $req)
			{
				$response = new ActionResponse();
				$response->set('isReqError', true);
				$response->set('requirements', $requirements);
				return $response;
			}
		}

		return new ActionRedirectResponse('install', 'license');
	}

	public function license()
	{
		if ($lastStep = $this->verifyStep())
		{
			return $lastStep;
		}

		$response = new ActionResponse('license', file_get_contents(ClassLoader::getRealPath('.') . 'license.txt'));
		$response->set('form', $this->buildLicenseForm());
		return $response;
	}

	public function acceptLicense()
	{
		if (!$this->buildLicenseValidator()->isValid())
		{
			return new ActionRedirectResponse('install', 'license');
		}

		return new ActionRedirectResponse('install', 'database');
	}

	public function database()
	{
		if ($lastStep = $this->verifyStep())
		{
			return $lastStep;
		}

		$response = new ActionResponse('form', $this->buildDatabaseForm());

		return $response;
	}

	public function setDatabase()
	{
		if (!$this->buildDatabaseValidator()->isValid())
		{
			return new ActionRedirectResponse('install', 'database');
		}

		$dsn = 'mysql://' .
				   $this->request->get('username') .
				   		($this->request->get('password') ? ':' . $this->request->get('password') : '') .
				   			'@' . $this->request->get('server') .
				   				'/' . $this->request->get('name');

		ClassLoader::import('library.activerecord.ActiveRecord');
		ActiveRecord::resetDBConnection();
		ActiveRecord::setDSN($dsn);

		try
		{
			$conn = ActiveRecord::getDBConnection();

			// test if InnoDB tables can be created
			$table = 'TestInnoDB';
			$create = 'CREATE TABLE ' . $table . ' (ID INTEGER) ENGINE = INNODB';
			$drop = 'DROP TABLE ' . $table;

			ActiveRecord::executeUpdate($create);
			$data = ActiveRecord::getDataBySQL('SHOW TABLE STATUS');
			ActiveRecord::executeUpdate($drop);

			foreach ($data as $row)
			{
				if (strtolower($row['Name']) == strtolower($table))
				{
					if (strtolower($row['Engine']) != 'innodb')
					{
						throw new SQLException('', $this->translate('_err_innodb_not_available'));
					}
				}
			}

			$dsnFile = ClassLoader::getRealPath('storage.configuration') . '/database.php';
			if (!file_exists(dirname($dsnFile)))
			{
				mkdir(dirname($dsnFile), 0777, true);
			}

			file_put_contents($dsnFile, '<?php return ' . var_export($dsn, true) . '; ?>');

			ActiveRecord::beginTransaction();

			// import schema
			Installer::loadDatabaseDump(file_get_contents(ClassLoader::getRealPath('installdata.sql') . '/create.sql'));

			// create root category
			Installer::loadDatabaseDump(file_get_contents(ClassLoader::getRealPath('installdata.sql') . '/initialData.sql'));

			// load US states
			Installer::loadDatabaseDump(file_get_contents(ClassLoader::getRealPath('installdata.sql.state') . '/US.sql'));

			ActiveRecord::commit();

			return new ActionRedirectResponse('install', 'admin');
		}
		catch (SQLException $e)
		{
			$validator = $this->buildDatabaseValidator();
			$validator->triggerError('connect', $e->getNativeError());
			$validator->saveState();

			return new ActionRedirectResponse('install', 'database');
		}
	}

	public function admin()
	{
		if ($lastStep = $this->verifyStep())
		{
			return $lastStep;
		}

		return new ActionResponse('form', $this->buildAdminForm());
	}

	public function setAdmin()
	{
		if (!$this->buildAdminValidator()->isValid())
		{
			return new ActionRedirectResponse('install', 'admin');
		}

		ClassLoader::import('application.model.user.UserGroup');
		ClassLoader::import('application.model.user.User');
		ClassLoader::import('application.model.user.SessionUser');

		ActiveRecordModel::beginTransaction();

		// create user group for administrators
		$group = UserGroup::getNewInstance('Administrators');
		$group->setAllRoles();
		$group->save();

		// create administrator account
		$user = User::getNewInstance($this->request->get('email'), null, $group);
		$user->loadRequestData($this->request);
		$user->setPassword($this->request->get('password'));
		$user->isEnabled->set(true);
		$user->save();

		ActiveRecordModel::commit();

		// log in
		SessionUser::setUser($user);

		// set store email
		$this->config->set('MAIN_EMAIL', $this->request->get('email'));
		$this->config->set('NOTIFICATION_EMAIL', $this->request->get('email'));
		$this->config->set('NEWSLETTER_EMAIL', $this->request->get('email'));
		$this->config->save();

		return new ActionRedirectResponse('install', 'config');
	}

	public function config()
	{
		if ($lastStep = $this->verifyStep())
		{
			return $lastStep;
		}

		$form = $this->buildConfigForm();

		$form->set('language', 'en');
		$form->set('curr', 'USD');

		// get all Locale languages
		$languages = $this->locale->info()->getAllLanguages();
		asort($languages);

		$response = new ActionResponse('form', $form);
		$response->set('languages', $languages);
		$response->set('currencies', $this->locale->info()->getAllCurrencies());
		return $response;
	}

	public function setConfig()
	{
		if (!$this->buildConfigValidator()->isValid())
		{
			return new ActionRedirectResponse('install', 'config');
		}

		Language::deleteCache();

		// site name
		$this->config->setValueByLang('STORE_NAME', $this->request->get('language'), $this->request->get('name'));
		$this->config->save();

		ClassLoader::import('application.model.Currency');

		// create currency
		if (ActiveRecord::objectExists('Currency', $this->request->get('curr')))
		{
			$currency = Currency::getInstanceByID($this->request->get('curr'), Currency::LOAD_DATA);
		}
		else
		{
			$currency = ActiveRecord::getNewInstance('Currency');
			$currency->setID($this->request->get('curr'));
			$currency->isEnabled->set(true);
			$currency->isDefault->set(true);
			$currency->save(ActiveRecord::PERFORM_INSERT);
		}

		ClassLoader::import('application.model.system.Language');

		// create language
		if (ActiveRecord::objectExists('Language', $this->request->get('language')))
		{
			$language = Language::getInstanceByID($this->request->get('language'), Language::LOAD_DATA);
		}
		else
		{
			$language = ActiveRecord::getNewInstance('Language');
			$language->setID($this->request->get('language'));
			$language->save(ActiveRecord::PERFORM_INSERT);

			$language->isEnabled->set(true);
			$language->isDefault->set(true);
			$language->save();
		}

		// set root category name to "LiveCart"
		ClassLoader::import('application.model.category.Category');
		$root = Category::getInstanceById(Category::ROOT_ID, Category::LOAD_DATA);
		$root->setValueByLang('name', $language->getID(), 'LiveCart');
		$root->save();

	 	// create a default shipping service
		ClassLoader::import('application.model.delivery.DeliveryZone');
		ClassLoader::import('application.model.delivery.ShippingService');
		ClassLoader::import('application.model.delivery.ShippingRate');

		$service = ShippingService::getNewInstance(DeliveryZone::getDefaultZoneInstance(), 'Default Service', ShippingService::SUBTOTAL_BASED);
		$service->save();

	 	$rate = ShippingRate::getNewInstance($service, 0, 100000);
	 	$rate->flatCharge->set(10);
	 	$rate->save();

		// create a couple of blank static pages
		ClassLoader::import('application.model.staticpage.StaticPage');
		$page = StaticPage::getNewInstance();
		$page->setValueByLang('title', $language->getID(), 'Contact Info');
		$page->setValueByLang('text', $language->getID(), 'Enter your contact information here');
		$page->isInformationBox->set(true);
		$page->save();

		$page = StaticPage::getNewInstance();
		$page->setValueByLang('title', $language->getID(), 'Shipping Policy');
		$page->setValueByLang('text', $language->getID(), 'Enter your shipping rate & policy information here');
		$page->isInformationBox->set(true);
		$page->save();

		// create an example site news post
		ClassLoader::import('application.model.sitenews.NewsPost');
		$news = ActiveRecordModel::getNewInstance('NewsPost');
		$news->setValueByLang('title', $language->getID(), 'Our store is open');
		$news->setValueByLang('text', $language->getID(), 'Powered by LiveCart software, we have gone live! Of course, we will have to go to <a href="../backend">the backend area</a> and add some categories and products first...');
		$news->setValueByLang('moreText', $language->getID(), 'Do not forget to delete this post when you actually go live :)');
		$news->isEnabled->set(true);
		$news->save();

		return new ActionRedirectResponse('install', 'finish');
	}

	public function finish()
	{
		if ($lastStep = $this->verifyStep())
		{
			return $lastStep;
		}

		$response = new ActionResponse();

		return $response;
	}

	private function verifyStep()
	{
		$steps = array('index', 'license', 'database', 'admin', 'config', 'finish');
		$steps = array_flip($steps);

		$lastStepFile = ClassLoader::getRealPath('cache') . '/installStep.php';

		if (file_exists($lastStepFile))
		{
			$lastStep = include $lastStepFile;
			if ($steps[$lastStep] > $steps[$this->request->getActionName()])
			{
				return new ActionRedirectResponse('install', $lastStep);
			}
		}

		file_put_contents($lastStepFile, '<?php return ' . var_export($this->request->getActionName(), true) . '; ?>');
	}

	/**
	 * @return RequestValidator
	 */
	private function buildLicenseValidator()
	{
		$validator = new RequestValidator("license", $this->request);
		$validator->addCheck("accept", new IsNotEmptyCheck($this->translate("You must accept the LiveCart license agreement to continue with the installation")));
		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildLicenseForm()
	{
		return new Form($this->buildLicenseValidator());
	}

	/**
	 * @return RequestValidator
	 */
	private function buildDatabaseValidator()
	{
		$validator = new RequestValidator("database", $this->request);
		$validator->addCheck("server", new IsNotEmptyCheck($this->translate("Please enter the database server host name")));
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("Please enter the database name")));
		$validator->addCheck("username", new IsNotEmptyCheck($this->translate("Please enter the database user name")));
		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildDatabaseForm()
	{
		return new Form($this->buildDatabaseValidator());
	}

	/**
	 * @return RequestValidator
	 */
	private function buildAdminValidator()
	{
		ClassLoader::import('application.helper.check.IsUniqueEmailCheck');

		$validator = new RequestValidator("createAdmin", $this->request);
		$validator->addCheck("firstName", new IsNotEmptyCheck($this->translate("Please enter the admin first name")));
		$validator->addCheck("lastName", new IsNotEmptyCheck($this->translate("Please enter the admin last name")));
		$validator->addCheck("email", new IsNotEmptyCheck($this->translate("Please enter the admin e-mail address")));
		$validator->addCheck("email", new IsUniqueEmailCheck($this->translate("The e-mail address is already assigned to an existing user account")));
		$validator->addCheck("password", new IsNotEmptyCheck($this->translate("Please enter the password")));
		$validator->addCheck("confirmPassword", new IsNotEmptyCheck($this->translate("Please enter the password")));
		$validator->addCheck("confirmPassword", new PasswordEqualityCheck($this->translate("Passwords do not match"), $this->request->get('password'), 'password'));
		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildAdminForm()
	{
		return new Form($this->buildAdminValidator());
	}

	/**
	 * @return RequestValidator
	 */
	private function buildConfigValidator()
	{
		$validator = new RequestValidator("installConfig", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("Please enter the name of your store")));
		$validator->addCheck("language", new IsNotEmptyCheck($this->translate("Please select the base language of your store")));
		$validator->addCheck("curr", new IsNotEmptyCheck($this->translate("Please select the base currency of your store")));

		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildConfigForm()
	{
		return new Form($this->buildConfigValidator());
	}
}

?>