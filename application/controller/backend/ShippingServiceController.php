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
 * @role delivery
 */
class ShippingServiceController extends StoreManagementController
{
	public function index() 
	{
	    if(($zoneID = (int)$this->request->getValue('id')) <= 0) 
	    {
	        $deliveryZoneArray = array('ID' => '');
	        $shippingServices = ShippingService::getByDeliveryZone()->toArray();
	    }
	    else
	    {
	        $deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
	        $deliveryZoneArray = $deliveryZone->toArray();
	        $shippingServices = $deliveryZone->getShippingServices()->toArray();
	    }
	    
	    
	      
		$form = $this->createShippingServiceForm();
		$form->setData(array('name_en' => 'test', 'rangeType' => 1));
		
		
		$response = new ActionResponse();
		$response->setValue('defaultLanguageCode', $this->store->getDefaultLanguageCode());
		$response->setValue('shippingServices', $shippingServices);
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageSetArray());
		$response->setValue('newService', array('DeliveryZone' => $deliveryZoneArray));
		$response->setValue('newRate', array('ShippingService' => array('DeliveryZone' => $deliveryZoneArray, 'ID' => '')));
		$response->setValue('deliveryZone', $deliveryZoneArray);
	    $response->setValue('defaultCurrencyCode', $this->store->getDefaultCurrency()->getID());
	    $response->setValue('form', $form);
	    return $response;
	}

	/**
	 * @role update
	 */
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
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageSetArray());
		$response->setValue('service', $shippingService->toArray());
		$response->setValue('shippingRates', $shippingService->getRates()->toArray());
		$response->setValue('newRate', array('ShippingService' => $shippingService->toArray()));
	    $response->setValue('defaultCurrencyCode', $this->store->getDefaultCurrency()->getID());
		$response->setValue('form', $form);
	    
	    return $response;
    }
    
    /**
     * @role update
     */
    public function create()
    {
        if(($deliveryZoneId = (int)$this->request->getValue('deliveryZoneID')) > 0)
        {
            $deliveryZone = DeliveryZone::getInstanceByID($deliveryZoneId, true);
        }
        else
        {
            $deliveryZone = null;
        }
     
        $shippingService = ShippingService::getNewInstance($deliveryZone, $this->request->getValue('name'), $this->request->getValue('rangeType'));

        return $this->save($shippingService);
    }
    
    /**
     * @role update
     */
    public function update()
    {
        $shippingService = ShippingService::getInstanceByID((int)$this->request->getValue('serviceID'));
        return $this->save($shippingService);
    }
    
    /**
     * @role update
     */
    public function validateRates()
    {
        $ratesData = $this->getRatesFromRequest();
        $errors = $this->validateRate('', $ratesData['']);
        return empty($errors) 
            ? new JSONResponse(array('status' => 'success')) 
            : new JSONResponse(array('status' => 'failure', 'errors' => $errors));
    }
    
    /**
     * @role update
     */
    public function sort()
    {
        echo $this->request->getValue('target');
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            echo $key;
           $shippingService = ShippingService::getInstanceByID((int)$key);
           $shippingService->position->set((int)$position);
           $shippingService->save();
        }

        return new JSONResponse(array('status' => 'success'));
    }

    private function isNotValid($name, $rates = array())
    {
        $errors = array();
        
        if($name == '')
        {
            $errors['name'] = $this->translate('_error_name_should_not_be_empty');
        }
        
        foreach($rates as $id => $rate)
        {
            if(!empty($id))
            {
                $errors = array_merge($errors, $this->validateRate($id, $rate));
            }
        }

        return empty($errors) ? false : $errors;
    }

    private function getRatesFromRequest()
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
    
    private function save(ShippingService $shippingService)
    {       
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
    
	private function createShippingServiceForm()
	{
		return new Form($this->createShippingServiceFormValidator());
	}
	
	private function createShippingServiceFormValidator()
	{	
		$validator = new RequestValidator('shippingService', $this->request);
		
		return $validator;
	}	
	
    private function validateRate($id, $rate)
    {
       $errors = array();   
       if($this->request->getValue('rangeType') == ShippingService::WEIGHT_BASED)
       {
           if(!is_numeric($rate['weightRangeStart'])) $errors["rate_" . $id . "_weightRangeStart"] = $this->translate('_error_range_start_should_be_a_float_value');
           if(!is_numeric($rate['weightRangeEnd'])) $errors["rate_" . $id . "_weightRangeEnd"] = $this->translate('_error_range_end_should_be_a_float_value');   
           if(!empty($rate['perKgCharge']) && !is_numeric($rate['perKgCharge'])) $errors["rate_" . $id . "_perKgCharge"] = $this->translate('_error_per_kg_charge_should_be_a_float_Value');   
       }
       else
       {
           if(!is_numeric($rate['subtotalRangeStart'])) $errors["rate_" . $id . "_subtotalRangeStart"] = $this->translate('_error_range_start_should_be_a_float_value');
           if(!is_numeric($rate['subtotalRangeEnd'])) $errors["rate_" . $id . "_subtotalRangeEnd"] = $this->translate('_error_range_end_should_be_a_float_value');
           if(!empty($rate['subtotalPercentCharge']) && !is_numeric($rate['subtotalPercentCharge'])) $errors["rate_" . $id . "_subtotalPercentCharge"] = $this->translate('_error_subtotal_percent_charge_should_be_a_float_value');   
       }
       
       if(!empty($rate['flatCharge']) && !is_numeric($rate['flatCharge'])) $errors["rate_" . $id . "_flatCharge"] = $this->translate('_error_flat_charge_should_be_a_float_value');
       if(!empty($rate['perItemCharge']) && !is_numeric($rate['perItemCharge'])) $errors["rate_" . $id . "_perItemCharge"] = $this->translate('_error_per_item_charge_should_be_a_float_value');
    
       return $errors;
    }
    
}
?>