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
		$validator->addCheck("accept", new IsNotEmptyCheck($this->translate("You must accept the LiveCart license agreement to continue with the installation")));
		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildDatabaseForm()
	{
		return new Form($this->buildLicenseValidator());
	}    
}

?>