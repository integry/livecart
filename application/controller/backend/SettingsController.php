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
		$c->updateSettings();
		
        $defLang = Store::getInstance()->getDefaultLanguageCode();
		$languages = Store::getInstance()->getLanguageArray(Store::INCLUDE_DEFAULT);
			
		$sectionId = $this->request->getValue('id');						
		$values = $c->getSettingsBySection($sectionId);
		
		$form = $this->getForm($values);
		$multiLingualValues = array();
		
		foreach ($values as $key => $value)
		{
    		if ($c->isMultiLingual($key))
    		{
                foreach ($languages as $lang)
                {
                    $form->setValue($key . ($lang != $defLang ? '_' . $lang : ''), $c->getValueByLang($key, $lang));    
                }                

                $multiLingualValues[$key] = true;
            }
            else
            {
                $form->setValue($key, $c->getValue($key));	
    		}
		}
				
		$response = new ActionResponse();
		$response->set('form', $form);
		$response->setValue('title', $this->translate($c->getSectionTitle($sectionId)));
		$response->setValue('values', $values);
		$response->setValue('id', $sectionId);
		$response->setValue('layout', $c->getSectionLayout($sectionId));		
		$response->setValue('multiLingualValues', $multiLingualValues);
		$response->setValue('languages', Store::getInstance()->getLanguageSetArray());
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
			$languages = Store::getInstance()->getLanguageArray();
            $defLang = Store::getInstance()->getDefaultLanguageCode();
                    
            $c->setAutoSave(false);
			foreach ($values as $key => $value)
			{
				if ($c->isMultiLingual($key))
				{
                    $c->setValueByLang($key, $defLang, $this->request->getValue($key));
                    foreach ($languages as $lang)
                    {
                        $c->setValueByLang($key, $lang, $this->request->getValue($key . '_' . $lang));
                    }
                }
                else
                {
                    $c->setValue($key, $this->request->getValue($key, 'bool' == $value['type'] ? 0 : ''));		                    
                }
			}  	
			
			$c->save();
			$c->setAutoSave(true);
			  
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