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
	    $response->setValue('countryGroups', $this->locale->info()->getCountryGroups());
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
	
	public function addMask()
	{
	    return new JSONResponse(array('status' => 'success', 'id' => rand(1, 100000)));
	}

	public function deleteMask()
	{
	    return new JSONResponse(array('status' => 'success'));
	}
	
}

?>