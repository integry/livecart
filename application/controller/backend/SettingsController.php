<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.system.Config");
ClassLoader::import('framework.request.validator.RequestValidator');
ClassLoader::import('framework.request.validator.Form');
ClassLoader::import('framework.request.validator.check.*');
ClassLoader::import('framework.request.validator.filter.*');
		
/**
 * Application settings management
 *
 * @package application.controller.backend
 *
 */
class SettingsController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function index()
	{
		// get settings tree 
		$c = new Config();
		
		$response = new ActionResponse();
		$response->setValue('categories', json_encode($c->getTree()));
		return $response;
	}
	
	/**
	 *	Individual settings section
	 */
	public function edit()
	{
		$sectionId = $this->request->getValue('id');
		
		$c = new Config();
		$section = $c->getSection($sectionId);		
		
		$values = array();
		foreach ($section as $key => $value)
		{
			if ('-' == $value || '+' == $value)
			{
			  	$type = 'bool';
			  	$value = 0 + ('-' == $value);
				//$value = 1 - ('-' == $value);			  	
			}  
			elseif (is_numeric($value))
			{
			  	$type = 'num';
			}
			elseif ('[' == substr($value, 0, 1) && ']' == substr($value, -1))
			{
			  	$type = 'array';
			}
			else
			{
			  	$type = 'string';
			}
			
			$values[$key] = array('type' => $type, 'value' => $value, 'title' => $key);
		}		
				
		$response = new ActionResponse();
		$response->set('form', $this->getForm($values));
		$response->setValue('values', $values);
		$response->setValue('layout', $c->getSectionLayout($sectionId));		
		return $response;	
	}  		  

	/**
	 *	Save settings
	 */
	public function save()
	{
		$validator = $this->getValidator();
		
		if (!$validator->isValid())
		{
		  	return new JSONResponse(array('errors' => $validator->getErrorList()));
		}
		else
		{
		  	return new JSONResponse(array('success' => true));		  	
		}
	}  		  
	
	private function getForm($settings)
	{
		return new Form($this->getValidator($settings));
	}

	private function getValidator($settings)
	{	
		$val = new RequestValidator('settings', $this->request);
		foreach ($settings as $key => $value)
		{
			if ('num' == $value['type'])
			{
				$val->addCheck($key, new IsNumericCheck($this->translate('_err_numeric')));
				$val->addFilter($key, new NumericFilter());
			}	
		}
		
		return $val;	  
	}
}

?>