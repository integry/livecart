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
	    $order = CustomerOrder::getInstanceById($this->request->get('id'), true, true);
        $order->loadItems();
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
	    
	    $shipableShipmentsCount = 0;
	    foreach($shipments as $shipment)
	    {
	        
	        $subtotalAmount += $shipment->amount->get();
	        $shippingAmount += $shipment->shippingAmount->get();
	        $taxAmount += $shipment->taxAmount->get();
	        
	        $shipmentsArray[$shipment->getID()] = $shipment->toArray();
	        
	        $rate = unserialize($shipment->shippingServiceData->get());
            if(is_object($rate))
            {
                $rate->setApplication($this->application);
	            $shipmentsArray[$shipment->getID()] = array_merge($shipmentsArray[$shipment->getID()], $rate->toArray());
	            $shipmentsArray[$shipment->getID()]['ShippingService']['ID'] = $shipmentsArray[$shipment->getID()]['serviceID'];
            }
            else
            {
                $shipmentsArray[$shipment->getID()]['ShippingService']['name_lang'] = $this->translate('_shipping_service_is_not_selected');
            }	
            
            if($shipment->status->get() != Shipment::STATUS_SHIPPED && $shipment->isShippable()) 
            {
                $shipableShipmentsCount++;
            }
	    }
	    
        $totalAmount = $subtotalAmount + $shippingAmount;
	    
	    $response = new ActionResponse();
	    $response->set('orderID', $this->request->get('id'));
	    $response->set('order', $order->toArray());
	    $response->set('shippingServiceIsNotSelected', $this->translate('_shipping_service_is_not_selected'));
	    $response->set('shipments', $shipmentsArray);
	    $response->set('subtotalAmount', $subtotalAmount);
	    $response->set('shippingAmount', $shippingAmount);	    
	    $response->set('downloadableShipment', $order->getDownloadShipment()->toArray());
	    $response->set('taxAmount', $taxAmount);
	    $response->set('totalAmount', $totalAmount);
        $response->set('shipableShipmentsCount', $shipableShipmentsCount);
	    $response->set('statuses', $statuses + array(-1 => $this->translate('_delete')));
	    unset($statuses[3]);
	    $response->set('statusesWithoutShipped', $statuses);
	    $response->set('newShipmentForm', $form);
	    return $response;
	}
	
	public function changeService()
	{
        $shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
        $shipment->loadItems();
        $order = $shipment->order->get();
        $zone = $shipment->order->get()->getDeliveryZone();        
        $shipmentRates = $zone->getShippingRates($shipment);
        
        $shipment->setAvailableRates($shipmentRates);
        
        $history = new OrderHistory($order, $this->user);
        
        $selectedRate = null;
        foreach($shipment->getAvailableRates() as $rate)
        {
            if($rate->getServiceID() == $this->request->get('serviceID'))
            {
                $selectedRate = $rate;
                break;
            }
        }
        
		$shipment->setRateId($this->request->get('serviceID'));
		
	    $shipment->save(ActiveRecord::PERFORM_UPDATE);

	    $shippingService = ShippingService::getInstanceByID($this->request->get('serviceID'));
	    
        $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
        $shipment->setRateId($shippingService->getID());
   
	    $shipment->shippingService->set($shippingService);
        $shipment->recalculateAmounts();
	    
	    $shipment->save();
	    
	    $history->saveLog();
	    
	    $shipmentArray = $shipment->toArray();
	    $shipmentArray['ShippingService']['ID'] = $this->request->get('serviceID');
	    
	    return new JSONResponse(array(
			    'shipment' => array(
		               'ID' => $shipment->getID(),
		               'amount' => $shipment->amount->get(),
		               'shippingAmount' => (float)$shipment->shippingAmount->get(),
		               'taxAmount' => $shipment->taxAmount->get(),
		               'total' => $shipment->shippingAmount->get() + $shipment->amount->get() + (float)$shipment->taxAmount->get(),
		               'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
		               'suffix' => $shipment->amountCurrency->get()->priceSuffix->get(),
		               'ShippingService' => $shipmentArray['ShippingService']
		           )
			),
			'success',
			$this->translate('_shipping_service_was_successfuly_changed')
		);
	}
	
	public function changeStatus()
	{
	    $status = (int)$this->request->get('status');
	    
	    $shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress'));
        $shipment->loadItems();
        
        $zone = $shipment->order->get()->getDeliveryZone();
        $shipmentRates = $zone->getShippingRates($shipment);
        $shipment->setAvailableRates($shipmentRates);
            
	    $history = new OrderHistory($shipment->order->get(), $this->user);
	    
	    $shipment->status->set($status);
	    $shipment->save();
	    
	    $history->saveLog();
	    
	    return new JSONResponse(false, 'success', $this->translate('_shipment_status_has_been_successfully_updated'));
	}
	
	public function getAvailableServices()
	{
	    if($shipmentID = (int)$this->request->get('id'))
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
	                'total' => (float)$shipment->taxAmount->get() + (float)$shipment->amount->get() + (float)$rateArray['costAmount'],
	                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
	                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
                );
            }
	        return new JSONResponse(array( 'services' => $shippingRatesArray));
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
	    $order = CustomerOrder::getInstanceByID((int)$this->request->get('orderID'), true, array('BillingAddress' => 'UserAddress', 'ShippingAddress' => 'UserAddress'));
	    $shipment = Shipment::getNewInstance($order);
	    
	    $history = new OrderHistory($order, $this->user);
	    
	    $response = $this->save($shipment);
	    
	    $history->saveLog();
	    
	    return $response;
    }
    
    /**
     * @role update
     */
    public function update()
    {
        $order = CustomerOrder::getInstanceByID((int)$this->request->get('ID'));
        return $this->save($order);
    }
    
    private function save(Shipment $shipment)
    {
        $validator = $this->createShipmentFormValidator();
		if ($validator->isValid())
		{   		
		    if($shippingServiceID = $this->request->get('shippingServiceID'))
		    {
			    $shippingService = ShippingService::getInstanceByID($shippingServiceID);
			    
			    $shipment->status->set((int)$this->request->get('status'));
			    $shipment->shippingService->set($shippingService);
			    $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
			    $shipment->setRateId($shippingService->getID());
		    }
		    else
		    {
		        $shipment->amountCurrency->set($shipment->order->get()->currency->get());
		    }
		    
    		$shipment->save();
    		
            return new JSONResponse(
	            array(
		            'shipment' => array(
		                'ID' => $shipment->getID(),
		                'amount' => $shipment->amount->get(),
		                'shippingAmount' => $shipment->shippingAmount->get(),
		                'ShippingService' => array('ID' => ($shipment->shippingService->get() ? $shipment->shippingService->get()->getID() : 0) ),
		                'taxAmount' => $shipment->taxAmount->get(),
		                'total' => $shipment->shippingAmount->get() + $shipment->amount->get() + (float)$shipment->taxAmount->get(),
		                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
		                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
		            )
	            ),
	            'success',
	            $this->translate('_new_shipment_has_been_successfully_created')
            );
		}
		else
		{
			return new JSONResponse(
			    array(
			        'errors' => $validator->getErrorList()
			    ),
			    'failure',
			    $this->translate('_error_creating_new_shipment')
		    );
		}
    }

    public function edit()
    {
        $group = ProductFileGroup::getInstanceByID((int)$this->request->get('id'), true);
        
        return new JSONResponse($group->toArray());
    }
    
    /**
     * @role update
     */
	public function delete()
	{
	    $shipment = Shipment::getInstanceByID('Shipment', (int)$this->request->get('id'), true, array('Order' => 'CustomerOrder'));
	    $history = new OrderHistory($shipment->order->get(), $this->user);
	    
	    $shipment->delete();
	    
	    $history->saveLog();
	    
	    return new JSONResponse(false, 'success');
	}
}

?>
