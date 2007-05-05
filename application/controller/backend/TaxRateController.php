<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.delivery.ShippingService");
ClassLoader::import("application.model.delivery.ShippingRate");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");
		
		
/**
 * Application settings management
 *
 * @package application.controller.backend
 *
 */
class TaxRateController extends StoreManagementController
{
	public function index() 
	{
	    if(!($zoneID = (int)$this->request->getValue('id'))) return;
	    
	    $deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
	      
		$form = $this->createShippingServiceForm();
		$form->setData(array('name_en' => 'test', 'rangeType' => 1) /* $deliveryZone->toArray() */);
		
		
		$response = new ActionResponse();
		$response->setValue('defaultLanguageCode', $this->store->getDefaultLanguageCode());
		$response->setValue('shippingServices', $deliveryZone->getShippingServices()->toArray());
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageSetArray(false, false));
		$response->setValue('newService', array('DeliveryZone' => $deliveryZone->toArray()));
		$response->setValue('newRate', array('ShippingService' => array('DeliveryZone' => $deliveryZone->toArray(), 'ID' => '')));
		$response->setValue('deliveryZone', $deliveryZone->toArray());
	    $response->setValue('form', $form);
	    return $response;
	}
	
	private function createShippingServiceForm()
	{
		return new Form($this->createShippingServiceFormValidator());
	}
	
	private function createShippingServiceFormValidator()
	{	
		$validator = new RequestValidator('shippingService', $this->request);
		
		return $validator;
	}	
	
    public function delete()
    {
        $service = ShippingService::getInstanceByID((int)$this->request->getValue('id'));
        $service->delete();
        
        return new JSONResponse(array('status' => 'success'));
    }
    
    public function edit()
    {
	    $shippingService = ShippingService::getInstanceByID($this->request->getValue('id'), true);
		
	    $form = $this->createShippingServiceForm();
		$form->setData($shippingService->toArray());
		$response = new ActionResponse();
		$response->setValue('defaultLanguageCode', $this->store->getDefaultLanguageCode());
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageSetArray(false, false));
		$response->setValue('service', $shippingService->toArray());
		$response->setValue('shippingRates', $shippingService->getRates()->toArray());
		$response->setValue('newRate', array('ShippingService' => $shippingService->toArray()));
	    $response->setValue('form', $form);
	    
	    return $response;
    }
    
    public function save()
    {
        if($serviceID = (int)$this->request->getValue('serviceID'))
        {
            $shippingService = ShippingService::getInstanceByID($serviceID);
        }
        else
        {
	        $deliveryZone = DeliveryZone::getInstanceByID($this->request->getValue('deliveryZoneID'), true);
	        $shippingService = ShippingService::getNewInstance($deliveryZone, $this->request->getValue('name'), $this->request->getValue('rangeType'));
        }
        
	    
        $ratesData = $this->getRatesFromRequest();
        $rates = array();
        if(!($errors = $this->isNotValid($this->request->getValue('name'), $ratesData)))
        {
	        $shippingService->setValueArrayByLang(array('name'), $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true, false), $this->request);      
		    $shippingService->save();
            
            foreach($ratesData as $id => $data)
            {
                if(preg_match('/^new/', $id))
                {
	                if($shippingService->rangeType->get() == ShippingService::WEIGHT_BASED)
	                {
	                    $rangeStart = $data['weightRangeStart'];
	                    $rangeEnd = $data['weightRangeEnd'];
	                } 
	                else if($shippingService->rangeType->get() == ShippingService::SUBTOTAL_BASED)
	                {
	                    $rangeStart = $data['subtotalRangeStart'];
	                    $rangeEnd = $data['subtotalRangeEnd'];
	                }
	                
	                $rate = ShippingRate::getNewInstance($shippingService, $rangeStart, $rangeEnd);
                } 
                else
                {
                    $rate = ShippingRate::getInstanceByID($id);
                }
                
                foreach($data as $var => $value)
                {
                    $rate->$var->set($value);
                }
                
                $rate->save();
            }
            
            return new JSONResponse(array('status' => 'success', 'service' => $shippingService->toArray()));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure', 'errors' => $errors));
        }
    }
    
    public function getRatesFromRequest()
    {
        $rates = array();
        foreach($_POST as $variable => $value)
        {
            $matches = array();
            if(preg_match('/^rate_([^_]*)_(perKgCharge|subtotalPercentCharge|perItemCharge|flatCharge|weightRangeEnd|weightRangeStart|subtotalRangeEnd|subtotalRangeStart)$/', $variable, $matches))
            {
                $id = $matches[1];
                $name = $matches[2];
                
                $rates[$id][$name] = $value;
            }
        }
        
        return $rates;
    }
}
?>