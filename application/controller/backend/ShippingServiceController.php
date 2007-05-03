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
class ShippingServiceController extends StoreManagementController
{
	public function index() 
	{
	    if(!($zoneID = (int)$this->request->getValue('id'))) return;
	    
	    $deliveryZone = DeliveryZone::getInstanceByID($zoneID, true);
	      
		$form = $this->createShippingServiceForm($deliveryZone);
		$form->setData($deliveryZone->toArray());
		
		$response = new ActionResponse();
		$response->setValue('shippingServices', ShippingService::getByDeliveryZone($deliveryZone)->toArray());
		$response->setValue('alternativeLanguagesCodes', $this->store->getLanguageArray());
		$response->setValue('newService', array('DeliveryZone' => $deliveryZone->toArray()));
		$response->setValue('newRate', array('ShippingService' => array('DeliveryZone' => $deliveryZone->toArray(), 'ID' => '')));
		$response->setValue('deliveryZone', $deliveryZone->toArray());
	    $response->setValue('form', $form);
	    return $response;
	}
	
	private function createShippingServiceForm(DeliveryZone $zone)
	{
		return new Form($this->createShippingServiceFormValidator($zone));
	}
	
	private function createShippingServiceFormValidator(DeliveryZone $zone)
	{	
		$validator = new RequestValidator('shippingService', $this->request);
		
		return $validator;
	}	
	
    public function delete()
    {
        return new RawResponse('delete');
    }
    
    public function edit()
    {
        return new RawResponse('edit');
    }
    
    public function save()
    {
        return new RawResponse('save');
    }
    
    public function sort()
    {
        return new RawResponse('sort');
    }
}
?>