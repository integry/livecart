<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role order
 */
class ShipmentController extends StoreManagementController 
{
	public function index()
	{
	    $order = CustomerOrder::getInstanceById($this->request->getValue('id'), true);
		$form = $this->createShipmentForm();
		$form->setData(array('orderID' => $order->getID()));
	    $shipments = $order->getShipments();
	    
	    $shippingServices = array();
	    foreach($order->getDeliveryZone()->getShippingServices() as $service) {
	        $shippingServices[$service->getID()]= $service->getValueByLang('name', $this->store->getDefaultLanguageCode(), true);
	    }
	
	    $statuses = array(
		    Shipment::STATUS_NEW => $this->translate('_shipping_status_new'),
		    Shipment::STATUS_PENDING => $this->translate('_shipping_status_pending'),
		    Shipment::STATUS_AWAITING => $this->translate('_shipping_status_awaiting'),
		    Shipment::STATUS_SHIPPED => $this->translate('_shipping_status_shipped')
	    );
	    
	    $subtotalAmount = 0; 
	    $shippingAmount = 0;
	    foreach($shipments as $shipment)
	    {
	        $subtotalAmount += $shipment->amount->get();
	        $shippingAmount += $shipment->shippingAmount->get();
	    }
        $totalAmount = $subtotalAmount + $shippingAmount;
	    
	    $response = new ActionResponse();
	    $response->setValue('orderID', $this->request->getValue('id'));
	    $response->setValue('order', $order->toArray());
	    $response->setValue('shipments', $shipments->toArray());
	    $response->setValue('shippingServices', $shippingServices);
	    $response->setValue('subtotalAmount', $subtotalAmount);
	    $response->setValue('shippingAmount', $shippingAmount);
	    $response->setValue('totalAmount', $totalAmount);
	    $response->setValue('statuses', $statuses);
	    $response->setValue('newShipmentForm', $form);
	    return $response;
	}
    
	private function createShipmentForm()
	{
		return new Form($this->createShipmentFormValidator());
	}
	
	private function createShipmentFormValidator()
	{	
		$validator = new RequestValidator('shippingService', $this->request);
		
		return $validator;
	}	
	
    /**
     * @role update
     */
    public function create()
    {
	    $order = CustomerOrder::getInstanceByID((int)$this->request->getValue('orderID'));
	    $shipment = Shipment::getNewInstance($order);
	    
	    return $this->save($shipment);
    }
    
    /**
     * @role update
     */
    public function update()
    {
        $order = CustomerOrder::getInstanceByID((int)$this->request->getValue('ID'));
        return $this->save($order);
    }
    
    private function save(Shipment $shipment)
    {
        $validator = $this->createShipmentFormValidator();
		if ($validator->isValid())
		{   		
		    $shippingService = ShippingService::getInstanceByID((int)$this->request->getValue('shippingServiceID'));
		    
		    $shipment->status->set((int)$this->request->getValue('status'));
		    $shipment->shippingService->set($shippingService);
		    $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
		    $shipment->setRateId($shippingService->getID());
		    
    		$shipment->save();
    		
            return new JSONResponse(array(
            'status' => "success", 
            'shipment' => array(
                'ID' => $shipment->getID(),
                'amount' => $shipment->amount->get(),
                'shippingAmount' => $shipment->shippingAmount->get()
            )));
		}
		else
		{
			return new JSONResponse(array('status' => "failure", 'errors' => $validator->getErrorList()));
		}
    }

    public function edit()
    {
        $group = ProductFileGroup::getInstanceByID((int)$this->request->getValue('id'), true);
        
        return new JSONResponse($group->toArray());
    }
    
    /**
     * @role update
     */
	public function delete()
	{
	    Shipment::getInstanceByID('Shipment', (int)$this->request->getValue('id'))->delete();
	    return new JSONResponse(array('status' => 'success'));
	}

    
}

?>