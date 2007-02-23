<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.system.Config");

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
		$form = $this->getForm($sectionId);
		
		$c = new Config();
		$section = $c->getSection($sectionId);		
		
		$values = array();
		foreach ($section as $key => $value)
		{
			if ('on' == $value || 'off' == $value)
			{
			  	$type = 'bool';
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
	
	private function getForm($sectionId)
	{
		$c = new Config();			  
		$s = $c->getSection($sectionId);
	}

	private function getValidator($sectionId)
	{
	  
	}
}

?>