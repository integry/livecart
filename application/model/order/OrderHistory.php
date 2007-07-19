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
				
        $array['shipments'] = array();
        foreach ($order->getShipments() as $shipment)
        {
            $shippingServiceArray = null;
            if($shipment->shippingService->get() && (int)$shipment->shippingService->get()->getID())
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
                'status' => (int)$shipment->status->get(), 
                'ShippingService' => $shippingServiceArray
            );
        } 
		
		$array['items'] = array();
		foreach ($order->getOrderedItems() as $item)
		{
		    $lng = $order->getApplication()->getDefaultLanguageCode();
            $name = $item->product->get()->name->get();

			$array['items'][$item->getID()] = array(
				'ID' => $item->getID(), 
                'shipmentID' => $item->shipment->get() ? $item->shipment->get()->getID() : null, 
				'count' => (int)$item->count->get(), 
				'Product' => array(
				    'ID' => (int)$item->product->get()->getID(),
                    'sku' =>$item->product->get()->sku->get(),
				    'name' => isset($name[$lng]) ? $name[$lng] : reset($name)
		        ),
				'Shipment' => $array['shipments'][$item->shipment->get()->getID()]
			);
		}
		
        $array['ShippingAddress'] = $order->shippingAddress->get()->toArray(true);
		$array['BillingAddress'] = $order->billingAddress->get()->toArray(true);
		
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
				$this->oldOrder['BillingAddress'], 
				$currentOrder['BillingAddress'], 
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
				$this->oldOrder['ShippingAddress'], 
				$currentOrder['ShippingAddress'], 
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
				OrderLog::ACTION_CANCELEDCHANGE, 
				$this->oldOrder, 
				$currentOrder, 
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
				$this->oldOrder, 
				$currentOrder, 
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
                $this->logShipment(null, $shipment, $currentOrder);
			}
		}
		// Delete shipment
		else if(count($this->oldOrder['shipments']) > count($currentOrder['shipments'])) // Delete shipment
		{
            foreach(array_diff_key($this->oldOrder['items'], $currentOrder['items']) as $item)
            {
                $this->logItem($item, null, $currentOrder);
            }
		    
            foreach(array_diff_key($this->oldOrder['shipments'], $currentOrder['shipments']) as $shipment)
            {
                $this->logShipment($shipment, null, $currentOrder);
            }
		}
		else
		{
    		foreach($currentOrder['shipments'] as $shipment)
            {
                if(isset($this->oldOrder['shipments'][$shipment['ID']]))
                {
                    $oldShipment = $this->oldOrder['shipments'][$shipment['ID']];
                    
                    // Change shipment status
                    if($oldShipment['status'] != $shipment['status'])
                    {
                        $this->logShipment($oldShipment, $shipment, $currentOrder);
                    }              
                    
                    // Change shipping service
                    if($oldShipment['ShippingService'] != $shipment['ShippingService'])
                    {
                        OrderLog::getNewInstance(
                            OrderLog::TYPE_SHIPMENT, 
                            OrderLog::ACTION_SHIPPINGSERVICECHANGE, 
                            $oldShipment, 
                            $shipment, 
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
                $this->logItem(null, $item, $currentOrder);
            }
            
            // Remove item
            foreach(array_diff_key($this->oldOrder['items'], $currentOrder['items']) as $item)
            {
                $this->logItem($item, null, $currentOrder);
            }
            
            foreach($currentOrder['items'] as $item)
            {
                if(isset($this->oldOrder['items'][$item['ID']]))
                {
                    $oldItem = $this->oldOrder['items'][$item['ID']];
                    
                    // Change shipping
                    if($oldItem['Shipment']['ID'] != $item['Shipment']['ID'])
                    {
                        OrderLog::getNewInstance(
                            OrderLog::TYPE_ORDERITEM, 
                            OrderLog::ACTION_SHIPMENTCHANGE, 
                            $oldItem, 
                            $item, 
                            $this->oldOrder['totalAmount'], 
                            $currentOrder['totalAmount'], 
                            $this->user, 
                            $this->currentOrder
                        )->save();
                    }
    
                    // Change item's quantity
                    if($oldItem['count'] != $item['count'])
                    {
                        $this->logItem($oldItem, $item, $currentOrder);
                    }
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
            $oldValue, 
            $newValue, 
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
            $oldValue, 
            $newValue, 
            $this->oldOrder['totalAmount'], 
            $orderArray['totalAmount'], 
            $this->user, 
            $this->currentOrder
        )->save();
    }
}
?>