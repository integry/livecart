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
		$response = new ActionResponse();
		$response->setValue('categories', json_encode(Config::getInstance()->getTree()));
		return $response;
	}
	
	/**
	 *	Individual settings section
	 */
	public function edit()
	{
		$c = Config::getInstance();

		$sectionId = $this->request->getValue('id');						
		$values = $c->getSettingsBySection($sectionId);
		
		$form = $this->getForm($values);
		
		foreach ($values as $key => &$value)
		{
			$value['value'] = $c->getValue($key);
			$form->setValue($key, $value['value']);	
		}
				
		$response = new ActionResponse();
		$response->set('form', $form);
		$response->setValue('title', $this->translate($c->getSectionTitle($sectionId)));
		$response->setValue('values', $values);
		$response->setValue('id', $sectionId);
		$response->setValue('layout', $c->getSectionLayout($sectionId));		
		return $response;	
	}  		  

	/**
	 *	Save settings
	 */
	public function save()
	{				
		$c = Config::getInstance();
		$values = $c->getSettingsBySection($this->request->getValue('id'));
		$validator = $this->getValidator($values);
		
		if (!$validator->isValid())
		{
		  	return new JSONResponse(array('errors' => $validator->getErrorList()));
		}
		else
		{
			foreach ($values as $key => $value)
			{
				$c->setValue($key, $this->request->getValue($key));		
			}  	
			
			$c->save();
			  
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