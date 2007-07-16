<?php
class OrderHistory
{
   const TYPE_ORDER = 0;
   const TYPE_SHIPMENT = 1;
   const TYPE_ORDERITEM = 2;
   const TYPE_SHIPPINGADDRESS = 3;
   const TYPE_BILLINGADDRESS = 4;
    
   const ACTION_ADD = 0;
   const ACTION_REMOVE = 1;
   const ACTION_CHANGE = 2;
   const ACTION_ORDER = 3;
   const ACTION_STATUSCHANGE = 4;
   
   private $oldObject;
   private $currentObject;
   
   /**
    * @var CustomerOrder
    */
   private $oldOrdert;
   
   /**
    * CustomerOrder
    */
   private $currentOrder;
   
   /**
    * @var User
    */
   private $user;
   
   public function __construct($object, CustomerOrder $order, User $user)
   {
       $this->currentObject = $object;
       $this->currentOrder = $order;
       $this->user = $user;
       
       
       // Clone. PHP native function clone() does only shallow copy of an object
       $this->oldObject = unserialize(serialize($this->currentObject));
       $this->oldOrder = unserialize(serialize($this->currentOrder));
   }
   
   public function saveLog()
   {
       $hasChanged = false;
       
       switch(get_class($this->currentObject))
       {
           case 'UserAddress':
		       if($this->currentObject->toArray(true) != $this->oldObject->toArray(true)) 
	           {
		           OrderLog::getNewInstance($this, self::TYPE_SHIPPINGADDRESS, self::ACTION_CHANGE, $this->oldObject->toString(), $this->currentObject->toString())->save();	
		       }
               break;
           case 'CustomerOrder':
               // Canceled
               if($this->currentObject->isCancelled->get() != $this->oldObject->isCancelled->get()) 
               {
                  OrderLog::getNewInstance($this, self::TYPE_ORDER, self::ACTION_STATUSCHANGE, (int)$this->oldObject->isCancelled->get(), (int)$this->currentObject->isCancelled->get())->save();	
               }
               
               // Status changed
		       if($this->oldOrder->status->get() != $this->currentOrder->status->get()) {
		           OrderLog::getNewInstance(self::TYPE_ORDER, self::ACTION_STATUSCHANGE, $this->user, (int)$this->oldOrder->status->get(), (int)$this->currentOrder->status->get(), $this->oldOrder, $this->currentOrder)->save();	
		       }
               break;
       }
   }
   
   public function getUser()
   {
       return $this->user;
   }
   
   public function getCurrentOrder()
   {
       return $this->currentOrder;
   }
   
   public function getOldOrder()
   {
       return $this->oldOrder;
   }
   
   private function logOrderStatus()
   {
   }
   
   private function logOrderAddresses()
   {
       $oldShippingAddress = $this->oldOrder->shippingAddress->get();
       $oldBillingAddress = $this->oldOrder->billingAddress->get();
       
       $currentShippingAddress = $this->currentOrder->shippingAddress->get();
       $currentBillingAddress = $this->currentOrder->billingAddress->get();
       
       if($currentShippingAddress && $currentShippingAddress->toArray(true) != $oldShippingAddress->toArray(true)) {
           OrderLog::getNewInstance(self::TYPE_SHIPPINGADDRESS, self::ACTION_CHANGE, $this->user, $oldShippingAddress->toString(), $currentShippingAddress->toString(), $this->oldOrder, $this->currentOrder)->save();	
       }

       if($currentBillingAddress && $currentBillingAddress->toArray(true) != $oldBillingAddress->toArray(true)) {
           OrderLog::getNewInstance(self::TYPE_BILLINGADDRESS, self::ACTION_CHANGE, $this->user, $oldBillingAddress->toString(), $currentBillingAddress->toString(), $this->oldOrder, $this->currentOrder)->save();	
       }
   }
   
   private function logShipments()
   {
//       $oldShipments = $this->oldOrder->getShipments();
//       $currentShipments = $this->currentOrder->getShipments();
//       
//       echo $oldShipments->getTotalRecordCount() . "\n";
//       echo $currentShipments->getTotalRecordCount() . "\n";
//       
//       for($i = 0; $i < $oldShipments->getTotalRecordCount(); $i++)
//       {
//           $currentShipment = Shipment::getInstanceByID('Shipment', $oldshipment->getID());
//           
//       }
       
   }
}
?>