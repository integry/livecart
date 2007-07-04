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
 * @role settings
 */
class SettingsController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function index()
	{
		$response = new ActionResponse();
		$response->set('categories', json_encode($this->config->getTree()));
		return $response;
	}
	
	/**
	 * Individual settings section
	 */
	public function edit()
	{
		$this->config->updateSettings();
		
        $defLang = $this->store->getDefaultLanguageCode();
		$languages = $this->store->getLanguageArray(Store::INCLUDE_DEFAULT);
			
		$sectionId = $this->request->get('id');						
		$values = $this->config->getSettingsBySection($sectionId);
		
		$form = $this->getForm($values);
		$multiLingualValues = array();
		
		foreach ($values as $key => $value)
		{
    		if ($this->config->isMultiLingual($key) && 'string' == $value['type'])
    		{
                foreach ($languages as $lang)
                {
                    $form->set($key . ($lang != $defLang ? '_' . $lang : ''), $this->config->getValueByLang($key, $lang));    
                }                

                $multiLingualValues[$key] = true;
            }
            else
            {
                $form->set($key, $this->config->get($key));	
    		}
		}
				
		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('title', $this->translate($this->config->getSectionTitle($sectionId)));
		$response->set('values', $values);
		$response->set('id', $sectionId);
		$response->set('layout', $this->config->getSectionLayout($sectionId));		
		$response->set('multiLingualValues', $multiLingualValues);
		return $response;	
	}  		  

	/**
	 * @role update
	 */
	public function save()
	{				
		$values = $this->config->getSettingsBySection($this->request->get('id'));
		$validator = $this->getValidator($values);
		
		if (!$validator->isValid())
		{
		  	return new JSONResponse(array('errors' => $validator->getErrorList()));
		}
		else
		{
			$languages = $this->store->getLanguageArray();
            $defLang = $this->store->getDefaultLanguageCode();
                    
            $this->config->setAutoSave(false);
			foreach ($values as $key => $value)
			{
				if ($this->config->isMultiLingual($key) && 'string' == $value['type'])
				{
                    $this->config->setValueByLang($key, $defLang, $this->request->get($key));
                    foreach ($languages as $lang)
                    {
                        $this->config->setValueByLang($key, $lang, $this->request->get($key . '_' . $lang));
                    }
                }
                else
                {
                    $this->config->set($key, $this->request->get($key, 'bool' == $value['type'] ? 0 : ''));		                    
                }
			}  	
			
			$this->config->save();
			$this->config->setAutoSave(true);
			  
			return new JSONResponse(array('success' => true));		  	
		}
	}  		  
	
	private function getForm($settings)
	{
		$form = new Form($this->getValidator($settings));
		
		// set multi-select values
		foreach ($settings as $key => $value)
		{
            if ('multi' == $value['extra'])
            {
                $values = $this->config->get($value['title']);

                foreach ($values as $key => $val)
                {
                    $form->set($value['title'] . '[' . $key . ']', 1);                    
                }
            }   
        }

		return $form;
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