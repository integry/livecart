<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.delivery.*");
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
	    $order = CustomerOrder::getInstanceById($this->request->getValue('id'), true, true);
		$form = $this->createShipmentForm();
		$form->setData(array('orderID' => $order->getID()));
	    $shipments = $order->getShipments();
        $zone = $order->getDeliveryZone();
	
	
	    $statuses = array(
		    Shipment::STATUS_NEW => $this->translate('_shipping_status_new'),
		    Shipment::STATUS_PENDING => $this->translate('_shipping_status_pending'),
		    Shipment::STATUS_AWAITING => $this->translate('_shipping_status_awaiting'),
		    Shipment::STATUS_SHIPPED => $this->translate('_shipping_status_shipped')
	    );
	    
	    $subtotalAmount = 0; 
	    $shippingAmount = 0;
	    $taxAmount = 0;
	    $shipmentsArray = array();
	    
	    foreach($shipments as $shipment)
	    {
	        $subtotalAmount += $shipment->amount->get();
	        $shippingAmount += $shipment->shippingAmount->get();
	        $taxAmount += $shipment->taxAmount->get();
	        
	        $shipmentsArray[$shipment->getID()] = $shipment->toArray();
	        
	        if(!$shipment->shippingService->get())
	        {
	            $rate = unserialize($shipment->shippingServiceData->get());
	            if (is_object($rate))
	            {
                    $rate = $rate->toArray();    
                    $shipmentsArray[$shipment->getID()]['ShippingService']['ID'] = $rate['serviceID'];
                }
	        }
	    }
	    
	    
        $totalAmount = $subtotalAmount + $shippingAmount;
	    
	    $response = new ActionResponse();
	    $response->setValue('orderID', $this->request->getValue('id'));
	    $response->setValue('order', $order->toArray());
	    $response->setValue('shipments', $shipmentsArray);
	    $response->setValue('subtotalAmount', $subtotalAmount);
	    $response->setValue('shippingAmount', $shippingAmount);
	    $response->setValue('taxAmount', $taxAmount);
	    $response->setValue('totalAmount', $totalAmount);
	    $response->setValue('statuses', $statuses + array(-1 => $this->translate('_delete')));
	    unset($statuses[3]);
	    $response->setValue('statusesWithoutShipped', $statuses);
	    $response->setValue('newShipmentForm', $form);
	    return $response;
	}
	
	public function changeService()
	{
	    
	    $shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->getValue('id'), true, array('Order' => 'CustomerOrder'));
	    $shipment->loadItems();
	    
        $zone = $shipment->order->get()->getDeliveryZone();
        $rates = $zone->getShippingRates($shipment);
        $shipment->setAvailableRates($rates);
            			
		if (!$rates->getByServiceId($this->request->getValue('serviceID')))
		{
			throw new ApplicationException('No rate found: ' . $shipment->getID() .' (' . $this->request->getValue('serviceID') . ')');
			return new ActionRedirectResponse('checkout', 'shipping');
		}
		
		$shipment->setRateId($this->request->getValue('serviceID'));
	    $shipment->save(ActiveRecord::PERFORM_UPDATE);
			
	    die();
	    
	    
	    //$shippingService = ShippingService::getInstanceByID($this->request->getValue('serviceID'));
	    
	    
        $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
        $shipment->setRateId($shippingService->getID());
        
        
	    $shipment->shippingService->set($shippingService);
        $shipment->recalculateAmounts();
	    
	    $shipment->save();
	    
	    return new JSONResponse(array('status' => 'suckless'));
	}
	
	public function changeStatus()
	{
	    $status = (int)$this->request->getValue('status');
	    
	    $shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->getValue('id'));
	    $shipment->status->set($status);
	    
	    $shipment->save();
	    
	    return new JSONResponse(array('status' => 'success'));
	}
	
	public function getAvailableServices()
	{
	    if($shipmentID = (int)$this->request->getValue('id'))
	    {
	        
	        $shipment = Shipment::getInstanceByID('Shipment', $shipmentID, true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
            $shipment->loadItems();
            
            
            $zone = $shipment->order->get()->getDeliveryZone();
            $shipmentRates = $zone->getShippingRates($shipment);
            $shipment->setAvailableRates($shipmentRates);
            
            $shippingRatesArray = array();
            foreach($shipment->getAvailableRates() as $rate)
            {
                $rateArray = $rate->toArray();
                $shippingRatesArray[$rateArray['serviceID']] = $rateArray;
                $shippingRatesArray[$rateArray['serviceID']]['shipment'] = array(
	                'ID' => $shipment->getID(),
	                'amount' => $shipment->amount->get(),
	                'shippingAmount' => (float)$rateArray['costAmount'],
	                'taxAmount' => $shipment->taxAmount->get(),
	                'total' => $shipment->shippingAmount->get() + $shipment->amount->get() + (float)$rateArray['costAmount'],
	                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
	                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
                );
            }
	        return new JSONResponse(array(
		        'services' => $shippingRatesArray, 
            ));
	    }
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
                'shippingAmount' => $shipment->shippingAmount->get(),
                'ShippingService' => array('ID' => $shipment->shippingService->get()->getID() ),
                'taxAmount' => $shipment->taxAmount->get(),
                'total' => $shipment->shippingAmount->get() + $shipment->amount->get() + (float)$shipment->taxAmount->get(),
                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
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