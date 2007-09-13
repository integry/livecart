<?php

ClassLoader::import('application.model.system.Installer');
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");
		
class InstallController extends FrontendController
{
    public function init()
	{
	  	$this->setLayout('install');
	    
		
//	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
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
		ActiveRecord::setDSN($dsn);
		
		try
		{
			$conn = ActiveRecord::getDBConnection();	
			
			$dsnFile = ClassLoader::getRealPath('storage.configuration') . '/database.php';
			if (!file_exists(dirname($dsnFile)))
			{
				mkdir(dirname($dsnFile), 0777, true);
			}
			
			$dsnArray = array('production' => $dsn, 'development' => $dsn, 'test' => $dsn);
			file_put_contents($dsnFile, '<?php return ' . var_export($dsnArray, true) . '; ?>');
			
			// import schema
            
            // initial data			
			
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
		$user->save();
		
		ActiveRecordModel::commit();		
		
		// log in
		SessionUser::setUser($user);
		
		return new ActionRedirectResponse('install', 'config');
	}
    
    public function config()
    {
        $form = $this->buildConfigForm();
        $form->set('language', 'en');
        $form->set('currency', 'USD');
        
		// get all Locale languages
		$languages = $this->locale->info()->getAllLanguages();
		asort($languages);
                
        $response = new ActionResponse('form', $form);
        $response->set('languages', $languages);
        $response->set('currencies', $this->locale->info()->getAllCurrencies());
        return $response;
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
		$validator->addCheck("sitename", new IsNotEmptyCheck($this->translate("Please enter the name of your store")));
		$validator->addCheck("language", new IsNotEmptyCheck($this->translate("Please select the base language of your store")));
		$validator->addCheck("currency", new IsNotEmptyCheck($this->translate("Please select the base currency of your store")));

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