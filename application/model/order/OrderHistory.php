<?php
class OrderHistory
{
   private $oldOrder;
   
   /**
    * CustomerOrder
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
   			$array['items'][$item->product->get()->getID()] = array(
   				'ID' => $item->getID(), 
   				'count' => $item->count->get(), 
   				'Product' => $item->product->get()->getID()
			);
   		}
   		
		$array['shipments'] = array();
   		foreach ($order->getShipments() as $shipment)
   		{
   			$array['shipments'][$shipment->getID()] = array(
   				'ID' => $shipment->getID(), 
   				'ShippingService' => $shipment->getShippingService() ? $shipment->getShippingService()->toArray() : null
			);
   		}
		
		$array['BillingAddress'] = $order->billingAddress->get()->toArray();
		$array['ShippingAddress'] = $order->shippingAddress->get()->toArray();
   	
   		return $array;
   }
   
   public function saveLog()
   {
       $currentOrder = $this->serializeToArray($this->currentOrder);
	
	   // Shipping address
       if($currentOrder['BillingAddress'] != $this->oldOrder['BillingAddress']) 
       {
           OrderLog::getNewInstance(
			   OrderLog::TYPE_SHIPPINGADDRESS, 
			   OrderLog::ACTION_CHANGE, 
			   print_r($this->oldOrder['BillingAddress'], true), 
			   print_r($currentOrder['BillingAddress']), 
			   $this->oldOrder['totalAmount'], 
			   $currentOrder['totalAmount'], 
			   $this->user, 
			   $currentOrder
		   )->save();	
       }
	   
	   // Billing address
       if($currentOrder['ShippingAddress'] != $this->oldOrder['ShippingAddress']) 
       {
           OrderLog::getNewInstance(
			   OrderLog::TYPE_SHIPPINGADDRESS, 
			   OrderLog::ACTION_CHANGE, 
			   print_r($this->oldOrder['ShippingAddress'], true), 
			   print_r($currentOrder['ShippingAddress']), 
			   $this->oldOrder['totalAmount'], 
			   $currentOrder['totalAmount'], 
			   $this->user, 
			   $currentOrder
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
			  $currentOrder->oldOrder['totalAmount'], 
			  $this->user, 
			  $currentOrder
		  )->save();	
       }
		  
	   // Create shippment
       if(count($this->oldOrder['shipments']) < count($currentOrder['shipments'])) 
   	   {
   	   	   foreach(array_diff_key($currentOrder['shipments'], $this->oldOrder['shipments']) as $shipment)
		   {
           	   OrderLog::getNewInstance(OrderLog::TYPE_SHIPMENT, OrderLog::ACTION_ADD, '', $shipment['ID'], $this->oldOrder['totalAmount'], $currentOrder['totalAmount'], $this->user, $this->currentOrder)->save();	
		   }
       }

	   // Delete shipment
       if(count($this->oldOrder['shipments']) > count($currentOrder['shipments'])) 
   	   {
   	   	   foreach(array_diff_key($this->oldOrder['shipments'], $currentOrder['shipments']) as $shipment)
		   {
           	   OrderLog::getNewInstance(OrderLog::TYPE_SHIPMENT, OrderLog::ACTION_REMOVE, $shipment['ID'], '', $this->oldOrder['totalAmount'], $currentOrder['totalAmount'], $this->user, $this->currentOrder)->save();	
		   }
       }
   }
}
?>