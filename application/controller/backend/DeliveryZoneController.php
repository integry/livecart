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
		$zones[] = array('ID' => -1, 'name' => $this->translate('_default_zone'));
		foreach(DeliveryZone::getAll()->toArray() as $zone) 
		{
		    $zones[] = array('ID' => $zone['ID'], 'name' => $zone['name']);
		}
		    
		$response = new ActionResponse();
		$response->setValue('zones', json_encode($zones));
	    $response->setValue('countryGroups', json_encode($this->locale->info()->getCountryGroups()));
		return $response;
	}
	
	public function countriesAndStates() 
	{
	    if(($id = (int)$this->request->getValue('id')) <= 0) 
	    {
	        return;
	    }
	    
	    $deliveryZone = DeliveryZone::getInstanceByID($id, true);
	    $localeInfo = $this->locale->info();
	    $allCountries = $localeInfo->getAllCountries();
	    
	    $allStates = array();
	    foreach(State::getAllStates()->toArray() as $state)
	    {
	        $allStates[$state['ID']] = $localeInfo->getCountryName($state['countryID']) . ":" . $state['name'];
	    }
	    
	    $selectedCountries = array();
	    foreach($deliveryZone->getCountries()->toArray() as $country)
	    {
	        $selectedCountries[$country['countryCode']] = $allCountries[$country['countryCode']];
	        unset($allCountries[$country['countryCode']]);
	    }
	    
	    $selectedStates = array();
	    foreach($deliveryZone->getStates()->toArray() as $state)
	    {
	        $selectedStates[$state['State']['ID']] = $allStates[$state['State']['ID']];
	        unset($allStates[$state['State']['ID']]);
	    }
	    	    
		$alternativeLanguagesCodes = array();
		foreach ($this->store->getLanguageArray() as $lang)
		{
			$alternativeLanguagesCodes[$lang] = $this->locale->info()->getOriginalLanguageName($lang);
		}
		
		$form = $this->createCountriesAndStatesForm($deliveryZone);
		$form->setData($deliveryZone->toArray());
	    
	    $response = new ActionResponse();
	    $response->setValue('form', $form);
	    $response->setValue('zoneID', $id);
	    $response->setValue('states', $allStates);
	    $response->setValue('countries', $allCountries);
	    $response->setValue('countryGroups', $this->locale->info()->getCountryGroups());
	    $response->setValue('selectedCountries', $selectedCountries);
	    $response->setValue('selectedStates', $selectedStates);
	    $response->setValue('zipMasks', $deliveryZone->getZipMasks()->toArray());
	    $response->setValue('cityMasks', $deliveryZone->getCityMasks()->toArray());
	    $response->setValue('addressMasks', $deliveryZone->getAddressMasks()->toArray());
	    $response->setValue('defaultLanguageCode', $this->store->getDefaultLanguageCode());
	    $response->setValue('alternativeLanguagesCodes', $alternativeLanguagesCodes);
	    
	    return $response;
	}

	
	public function taxRates() 
	{
	    if(!($id = (int)$this->request->getValue('id'))) return;
	    
	    return new RawResponse('Tax rates ' . $id);
	}
	
	private function createCountriesAndStatesForm(DeliveryZone $zone)
	{
		return new Form($this->createCountriesAndStatesFormValidator($zone));
	}

	private function createCountriesAndStatesFormValidator(DeliveryZone $zone)
	{	
		$validator = new RequestValidator('countriesAndStates', $this->request);
		
		return $validator;
	}
	
	
	public function saveStates()
	{
	    $zone = DeliveryZone::getInstanceByID((int)$this->request->getValue('id'));
	    DeliveryZoneState::removeByZone($zone);

	    foreach($this->request->getValue('active') as $activeStateID)
	    {
	        $state = State::getInstanceByID((int)$activeStateID);
	        $deliveryZoneState = DeliveryZoneState::getNewInstance($zone, $state);
	        $deliveryZoneState->save();
	    }
	    
	    return new JSONResponse(array('status' => 'success'));
	}
	
	public function saveCountries()
	{
	    $zone = DeliveryZone::getInstanceByID((int)$this->request->getValue('id'));
	    DeliveryZoneCountry::removeByZone($zone);

	    foreach($this->request->getValue('active') as $countryCode)
	    {
	        $deliveryZoneState = DeliveryZoneCountry::getNewInstance($zone, $countryCode);
	        $deliveryZoneState->save();
	    }
	    
	    return new JSONResponse(array('status' => 'success'));
	}

	public function save()
	{
	    if(($name = $this->request->getValue('name')) != '')
	    {
	        $zone = DeliveryZone::getNewInstance();
	        $zone->setValueByLang('name', $this->store->getDefaultLanguageCode(), $name);
	        $zone->save();
	        
	        return new JSONResponse(array('status' => 'success', 'ID' => $zone->getID()));
	    }
	    else
	    {
	        $zone = DeliveryZone::getInstanceByID((int)$this->request->getValue('id'));
	        foreach($this->store->getLanguageArray(true) as $langCode) 
	        {
	            $zone->setValueByLang('name', $langCode, $this->request->getValue('name_' . $langCode));
	        }
	        
	        $zone->save();
	    }
	    
	    
        return new JSONResponse(array('status' => 'success'));
	    
	}
	
	public function saveCityMask()
	{
	    if(($errors = $this->isValidMask()) === true)
	    {
		    $maskValue = $this->request->getValue('mask');
		    if($id = (int)$this->request->getValue('id'))
		    {
		        $mask = DeliveryZoneCityMask::getInstanceByID($id);
		        $mask->mask->set($maskValue);
		    }
		    else
		    {
		        $zone = DeliveryZone::getInstanceByID((int)$this->request->getValue('zoneID'));
		        $mask = DeliveryZoneCityMask::getNewInstance($zone, $maskValue);
		    }
		    
		    $mask->save();
		    
		    return new JSONResponse(array('status' => 'success', 'ID' => $mask->getID()));
	    }
	    else
	    {
		    return new JSONResponse(array('status' => 'failure', 'errors' => $errors));
	    }
	}
	
	private function isValidMask() {
	    if($this->request->getValue('mask')) 
        {
	        return true;
	    }
	    else 
	    {
	        return array('mask' => $this->translate('_error_mask_is_empty'));
	    }
	}

	public function delete()
	{
	    DeliveryZone::getInstanceByID((int)$this->request->getValue('id'))->delete();
	    
	    return new JSONResponse(array('status' => 'success'));
	}

	public function deleteCityMask()
	{
	    DeliveryZoneCityMask::getInstanceByID((int)$this->request->getValue('id'))->delete();
	    
	    return new JSONResponse(array('status' => 'success'));
	}
	
	public function saveZipMask()
	{	    
   	    if(($errors = $this->isValidMask()) === true)
	    {
		    $maskValue = $this->request->getValue('mask');
		    if($id = (int)$this->request->getValue('id'))
		    {
		        $mask = DeliveryZoneCityMask::getInstanceByID($id);
		        $mask->mask->set($maskValue);
		    }
		    else
		    {
		        $zone = DeliveryZone::getInstanceByID((int)$this->request->getValue('zoneID'));
		        $mask = DeliveryZoneZipMask::getNewInstance($zone, $maskValue);
		    }
		    
		    $mask->save();
		    
		    return new JSONResponse(array('status' => 'success', 'ID' => $mask->getID()));
	    }
	    else
	    {
		    return new JSONResponse(array('status' => 'failure', 'errors' => $errors));
	    }
	}

	public function deleteZipMask()
	{
	    DeliveryZoneZipMask::getInstanceByID((int)$this->request->getValue('id'))->delete();
	    
	    return new JSONResponse(array('status' => 'success'));
	}
	
	public function saveAddressMask()
	{
   	    if(($errors = $this->isValidMask()) === true)
	    {
	        $maskValue = $this->request->getValue('mask');
		    if($id = (int)$this->request->getValue('id'))
		    {
		        $mask = DeliveryZoneCityMask::getInstanceByID($id);
		        $mask->mask->set($maskValue);
		    }
		    else
		    {
		        $zone = DeliveryZone::getInstanceByID((int)$this->request->getValue('zoneID'));
		        $mask = DeliveryZoneAddressMask::getNewInstance($zone, $maskValue);
		    }
		    
		    $mask->save();
		    
		    return new JSONResponse(array('status' => 'success', 'ID' => $mask->getID()));
	    }
	    else
	    {
		    return new JSONResponse(array('status' => 'failure', 'errors' => $errors));
	    }
	}

	public function deleteAddressMask()
	{    
	    DeliveryZoneAddressMask::getInstanceByID((int)$this->request->getValue('id'))->delete();
	    
	    return new JSONResponse(array('status' => 'success'));
	}
}

?>