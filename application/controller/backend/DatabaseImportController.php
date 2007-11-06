<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import('library.import.LiveCartImporter');

/**
 * Handles dynamic interface customizations
 *
 * @package application.controller.backend
 * @author Integry Systems
 * 
 */
class DatabaseImportController extends StoreManagementController
{
	public function index()
	{
		$response = new ActionResponse();
		$response->set('form', $this->getForm());
		$response->set('carts', $this->getDrivers());
		$response->set('dbTypes', array('mysql' => 'MySQL'));
		$response->set('recordTypes', LiveCartImporter::getRecordTypes());		
		return $response;
	}
	
	public function import()
	{
        $validator = $this->getValidator();
        if (!$validator->isValid())
        {
            return new JSONResponse(array('errors' => $validator->getErrorList()));
        }        

		$dsn = $this->request->get('dbType') . '://' . 
			       $this->request->get('dbUser') . 
				   		($this->request->get('dbPass') ? ':' . $this->request->get('dbPass') : '') . 
				   			'@' . $this->request->get('dbServer') . 
				   				'/' . $this->request->get('dbName');
        
        try
		{
            $cart = $this->request->get('cart');
            ClassLoader::import('library.import.driver.' . $cart);
            $driver = new $cart($dsn, $this->request->get('filePath'));
        }
		catch (SQLException $e)
		{
            $validator->triggerError('dbServer', $e->getNativeError());
            $validator->saveState();		            
            return new JSONResponse(array('errors' => $validator->getErrorList()));
		}
		
        if (!$driver->isDatabaseValid())
        {
            $validator->triggerError('dbName', $this->maketext('_invalid_database', $driver->getName()));
            $validator->saveState();		            
            return new JSONResponse(array('errors' => $validator->getErrorList()));
		}

        if (!$driver->isPathValid())
        {
            $validator->triggerError('filePath', $this->maketext('_invalid_path', $driver->getName()));
            $validator->saveState();		            
            return new JSONResponse(array('errors' => $validator->getErrorList()));
		}
		
		@set_time_limit(0);		
		$importer = new LiveCartImporter($driver);
		
		// get importable data
		$types = $importer->getItemTypes();
		
    }
	
	private function getDrivers()
	{        
        $drivers = array();
        
        foreach (new DirectoryIterator(ClassLoader::getRealPath('library.import.driver')) as $file)
        {
            if (!$file->isDot())
            {
                include_once $file->getPathname();
                $className = basename($file->getFileName(), '.php');
                $drivers[$className] = call_user_func(array($className, 'getName'));
            }
        }

        return $drivers;
    }
	
	private function getForm()
	{
		ClassLoader::import('framework.request.validator.Form');
		return new Form($this->getValidator());
	}

	private function getValidator()
	{	
		ClassLoader::import('framework.request.validator.RequestValidator');
		ClassLoader::import('application.helper.filter.HandleFilter');
        		
		$val = new RequestValidator('databaseImport', $this->request);
		$val->addCheck('cart', new IsNotEmptyCheck($this->translate('_err_no_cart_selected')));
		$val->addCheck('dbServer', new IsNotEmptyCheck($this->translate('_err_no_database_server')));
		$val->addCheck('dbType', new IsNotEmptyCheck($this->translate('_err_no_db_type')));
		$val->addCheck('dbName', new IsNotEmptyCheck($this->translate('_err_no_database_name')));
		$val->addCheck('dbUser', new IsNotEmptyCheck($this->translate('_err_no_database_username')));
                		
		return $val;
	}	
}

?>