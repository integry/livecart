<?php
ClassLoader::import("library.I18Nv2.Country");
class OrderHistory
{
    private $oldOrder;
    
    /**
     * @var CustomerOrder
     */
    private $currentOrder;
    
    /**
     * @var User
     */
    private $user;
    
    public function __construct(CustomerOrder $order, User $user)
    {
		$order->loadAll();
			    
		$this->currentOrder = $order;
		$this->user = $user;
		
		$this->oldOrder = $this->serializeToArray($this->currentOrder);
    }
    
    private function serializeToArray(CustomerOrder $order)
    {	
		$array = array();
		
		$array['ID'] = $order->getID();
		$array['totalAmount'] = $order->totalAmount->get();
		$array['isCancelled'] = (int)$order->isCancelled->get();
		$array['status'] = (int)$order->status->get();
				
		$array['items'] = array();
		foreach ($order->getOrderedItems() as $item)
		{
			$array['items'][$item->getID()] = array(
				'ID' => $item->getID(), 
                'shipmentID' => $item->shipment->get() ? $item->shipment->get()->getID() : null, 
				'count' => $item->count->get(), 
				'Product' => $item->product->get()->getID()
			);
		}
		    	
		$array['shipments'] = array();
		foreach ($order->getShipments() as $shipment)
		{
		    $shippingServiceArray = null;
            if($shipment->shippingService->get())
            {
                $shippingServiceArray = $shipment->shippingService->get()->toArray();
            }
            else
            {
                $shippingService = unserialize($shipment->shippingServiceData->get());
                $shippingServiceArray = null;
                
                if(is_object($shippingService))
                {
                    $shippingService->setApplication($order->getApplication());
                    $shippingServiceArray = $shippingService->toArray();
                }
            }
            
		    $array['shipments'][$shipment->getID()] = array(
				'ID' => $shipment->getID(), 
                'status' => $shipment->status->get(), 
				'ShippingService' => $shippingServiceArray
			);
			
		} 
		
		$array['BillingAddress'] = $order->billingAddress->get()->toArray(true);
		$array['ShippingAddress'] = $order->shippingAddress->get()->toArray(true);
		    	
		return $array;
    }
    
    public function saveLog()
    {
		$currentOrder = $this->serializeToArray($this->currentOrder);
			
		// Billing address    
		if($currentOrder['BillingAddress'] != $this->oldOrder['BillingAddress']) 
		{
			OrderLog::getNewInstance(
				OrderLog::TYPE_BILLINGADDRESS, 
				OrderLog::ACTION_CHANGE, 
				print_r($this->oldOrder['BillingAddress'], true), 
				print_r($currentOrder['BillingAddress'], true), 
				$this->oldOrder['totalAmount'], 
				$currentOrder['totalAmount'], 
				$this->user, 
				$this->currentOrder
			)->save();	
		}
		
		// Shipping address
		if($currentOrder['ShippingAddress'] != $this->oldOrder['ShippingAddress']) 
		{
			OrderLog::getNewInstance(
				OrderLog::TYPE_SHIPPINGADDRESS, 
				OrderLog::ACTION_CHANGE, 
				print_r($this->oldOrder['ShippingAddress'], true), 
				print_r($currentOrder['ShippingAddress'], true), 
				$this->oldOrder['totalAmount'], 
				$currentOrder['totalAmount'], 
				$this->user, 
				$this->currentOrder
			)->save();	
		}
		
		// Canceled
		if($currentOrder['isCancelled'] != $this->oldOrder['isCancelled']) 
		{
			OrderLog::getNewInstance(
				OrderLog::TYPE_ORDER, 
				OrderLog::ACTION_STATUSCHANGE, 
				$this->oldOrder['isCancelled'], 
				$currentOrder['isCancelled'], 
				$this->oldOrder['totalAmount'], 
				$currentOrder['totalAmount'], 
				$this->user, 
				$this->currentOrder
			)->save();	
		}
		         
		// Status
		if($currentOrder['status'] != $this->oldOrder['status']) 
		{
			OrderLog::getNewInstance(
				OrderLog::TYPE_ORDER, 
				OrderLog::ACTION_STATUSCHANGE, 
				$this->oldOrder['status'], 
				$currentOrder['status'], 
				$this->oldOrder['totalAmount'], 
				$currentOrder['totalAmount'], 
				$this->user, 
				$this->currentOrder
			)->save();	
		}
				  
		// Create shippment
		if(count($this->oldOrder['shipments']) < count($currentOrder['shipments'])) 
		{
			foreach(array_diff_key($currentOrder['shipments'], $this->oldOrder['shipments']) as $shipment)
			{
                $this->logShipment(null, $shipment['ID'], $currentOrder);
			}
		}
		// Delete shipment
		else if(count($this->oldOrder['shipments']) > count($currentOrder['shipments'])) // Delete shipment
		{
            foreach(array_diff_key($this->oldOrder['shipments'], $currentOrder['shipments']) as $shipment)
            {
                $this->logShipment($shipment['ID'], null, $currentOrder);
            }
                
            foreach(array_diff_key($this->oldOrder['items'], $currentOrder['items']) as $item)
            {
                $this->logItem($item['ID'], null, $currentOrder);
            }
		}
		
		// Change shipment status
        foreach($currentOrder['shipments'] as $shipment)
        {
            if(isset($this->oldOrder['shipments'][$shipment['ID']]))
            {
                $oldShipment = $this->oldOrder['shipments'][$shipment['ID']];
                
                if($oldShipment['status'] != $shipment['status'])
                {
                    $this->logShipment($oldShipment['status'], $shipment['status'], $currentOrder);
                }
                
                if($oldShipment['ShippingService'] != $shipment['ShippingService'])
                {
                    OrderLog::getNewInstance(
                        OrderLog::TYPE_SHIPMENT, 
                        OrderLog::ACTION_SHIPPINGSERVICECHANGE, 
                        $oldShipment['ShippingService'] ? print_r($oldShipment['ShippingService'], true) : '', 
                        $shipment['ShippingService'] ? print_r($shipment['ShippingService'], true) : '', 
                        $this->oldOrder['totalAmount'], 
                        $currentOrder['totalAmount'], 
                        $this->user, 
                        $this->currentOrder
                    )->save();
                }
            }
        }
		
		// Add item
        foreach(array_diff_key($currentOrder['items'], $this->oldOrder['items']) as $item)
        {
            $this->logItem(null, $item['ID'], $currentOrder);
        }
        
        // Remove item
        foreach(array_diff_key($this->oldOrder['items'], $currentOrder['items']) as $item)
        {
            $this->logItem($item['ID'], null, $currentOrder);
        }
        
        foreach($currentOrder['items'] as $item)
        {
            if(isset($this->oldOrder['items'][$item['ID']]))
            {
                $oldItem = $this->oldOrder['items'][$item['ID']];
                
                // Change shipping
                if($oldItem['shipmentID'] != $item['shipmentID'])
                {
                    OrderLog::getNewInstance(
                        OrderLog::TYPE_ORDERITEM, 
                        OrderLog::ACTION_SHIPMENTCHANGE, 
                        $oldItem['shipmentID'], 
                        $item['shipmentID'], 
                        $this->oldOrder['totalAmount'], 
                        $currentOrder['totalAmount'], 
                        $this->user, 
                        $this->currentOrder
                    )->save();
                }

                // Change item's quantity
                if($oldItem['count'] != $item['count'])
                {
                    $this->logItem($oldItem['count'], $item['count'], $currentOrder);
                }
            }
        }
    }
    
    private function logItem($oldValue, $newValue, $orderArray)
    {
        $action = OrderLog::ACTION_COUNTCHANGE;
        if(!$oldValue)
        {
            $action = OrderLog::ACTION_ADD;
        }
        else if(!$newValue)
        {
            $action = OrderLog::ACTION_REMOVE;
        }
        
        OrderLog::getNewInstance(
            OrderLog::TYPE_ORDERITEM, 
            $action, 
            $oldValue ? $oldValue : '', 
            $newValue ? $newValue : '', 
            $this->oldOrder['totalAmount'], 
            $orderArray['totalAmount'], 
            $this->user, 
            $this->currentOrder
        )->save();
    }
 
    private function logShipment($oldValue, $newValue, $orderArray)
    {
        $action = OrderLog::ACTION_STATUSCHANGE;
        if(!$oldValue)
        {
            $action = OrderLog::ACTION_ADD;
        }
        else if(!$newValue)
        {
            $action = OrderLog::ACTION_REMOVE;
        }
        
        OrderLog::getNewInstance(
            OrderLog::TYPE_SHIPMENT, 
            $action, 
            $oldValue ? $oldValue : '', 
            $newValue ? $newValue : '', 
            $this->oldOrder['totalAmount'], 
            $orderArray['totalAmount'], 
            $this->user, 
            $this->currentOrder
        )->save();
    }
}
?>