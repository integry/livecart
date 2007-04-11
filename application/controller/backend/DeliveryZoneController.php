<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.delivery.State");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");
		
		
/**
 * Application settings management
 *
 * @package application.controller.backend
 *
 */
class DeliveryZoneController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function index()
	{
		$zones = array();
		foreach(DeliveryZone::getAll()->toArray() as $zone) 
		    $zones[] = array('ID' => $zone['ID'], 'name' => $zone['name']);
		
		    
		$response = new ActionResponse();
		$response->setValue('zones', json_encode($zones));
	    $response->setValue('countryGroups', json_encode($this->locale->info()->getCountryGroups()));
		return $response;
	}
	
	public function countriesAndStates() 
	{
	    if(!($id = (int)$this->request->getValue('id'))) return;
	    
	    $localeInfo = $this->locale->info();
	    $states = array();
	    foreach(State::getAllStates()->toArray() as $state)
	    {
	        $states[$state['ID']] = $localeInfo->getCountryName($state['countryID']) . ":" . $state['name'];
	    }
	    
	    $response = new ActionResponse();
	    $response->setValue('form', $this->createCountriesAndStatesForm());
	    $response->setValue('zoneID', $id);
	    $response->setValue('countries', $localeInfo->getAllCountries());
	    $response->setValue('states', $states);
	    return $response;
	}
	
	public function shippingRates() 
	{
	    if(!($id = (int)$this->request->getValue('id'))) return;
	    
	    return new RawResponse('Shipping rates ' . $id);
	}
	
	public function taxRates() 
	{
	    if(!($id = (int)$this->request->getValue('id'))) return;
	    
	    return new RawResponse('Tax rates ' . $id);
	}
	
	private function createCountriesAndStatesForm()
	{
		return new Form($this->createCountriesAndStatesFormValidator());
	}

	private function createCountriesAndStatesFormValidator()
	{	
		return new RequestValidator('countriesAndStates', $this->request);
	}
	
	
//	/**
//	 *	Individual settings section
//	 */
//	public function edit()
//	{
//		$c = Config::getInstance();
//		$c->updateSettings();
//		
//        $defLang = Store::getInstance()->getDefaultLanguageCode();
//		$languages = Store::getInstance()->getLanguageArray(Store::INCLUDE_DEFAULT);
//			
//		$sectionId = $this->request->getValue('id');						
//		$values = $c->getSettingsBySection($sectionId);
//		
//		$form = $this->getForm($values);
//		$multiLingualValues = array();
//		
//		foreach ($values as $key => $value)
//		{
//    		if ($c->isMultiLingual($key))
//    		{
//                foreach ($languages as $lang)
//                {
//                    $form->setValue($key . ($lang != $defLang ? '_' . $lang : ''), $c->getValueByLang($key, $lang));    
//                }                
//
//                $multiLingualValues[$key] = true;
//            }
//            else
//            {
//                $form->setValue($key, $c->getValue($key));	
//    		}
//		}
//				
//		$response = new ActionResponse();
//		$response->set('form', $form);
//		$response->setValue('title', $this->translate($c->getSectionTitle($sectionId)));
//		$response->setValue('values', $values);
//		$response->setValue('id', $sectionId);
//		$response->setValue('layout', $c->getSectionLayout($sectionId));		
//		$response->setValue('multiLingualValues', $multiLingualValues);
//		$response->setValue('languages', Store::getInstance()->getLanguageSetArray());
//		return $response;	
//	}  		  
//
//	/**
//	 *	Save settings
//	 */
//	public function save()
//	{				
//		$c = Config::getInstance();
//		$values = $c->getSettingsBySection($this->request->getValue('id'));
//		$validator = $this->getValidator($values);
//		
//		if (!$validator->isValid())
//		{
//		  	return new JSONResponse(array('errors' => $validator->getErrorList()));
//		}
//		else
//		{
//			$languages = Store::getInstance()->getLanguageArray();
//            $defLang = Store::getInstance()->getDefaultLanguageCode();
//                    
//            $c->setAutoSave(false);
//			foreach ($values as $key => $value)
//			{
//				if ($c->isMultiLingual($key))
//				{
//                    $c->setValueByLang($key, $defLang, $this->request->getValue($key));
//                    foreach ($languages as $lang)
//                    {
//                        $c->setValueByLang($key, $lang, $this->request->getValue($key . '_' . $lang));
//                    }
//                }
//                else
//                {
//                    $c->setValue($key, $this->request->getValue($key, 'bool' == $value['type'] ? 0 : ''));		                    
//                }
//			}  	
//			
//			$c->save();
//			$c->setAutoSave(true);
//			  
//			return new JSONResponse(array('success' => true));		  	
//		}
//	}  		  
//	

//	
	
}

?>