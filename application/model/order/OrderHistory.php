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
   
   /**
    * @var CustomerOrder
    */
   public $oldOrder;
   
   /**
    * @var CustomerOrder
    */
   public $currentOrder;
   
   /**
    * @var User
    */
   private $user;
   
   public function __construct(CustomerOrder $order, User $user)
   {
       $this->currentOrder = $order;
       $this->user = $user;
       
       // Clone. PHP native function clone() does only shallow copy of an object
       $this->oldOrder = unserialize(serialize($order));
   }
   
   public function saveLog()
   {
       $hasChanged = false;
       $currentTime = new ARSerializableDateTime();

       $this->logOrderCanceled();
       $this->logOrderStatus();
       $this->logOrderAddresses();
       $this->logShipments();
   }
   
   private function logOrderCanceled()
   {
          if($this->oldOrder->isCancelled->get() != $this->currentOrder->isCancelled->get()) {
           OrderLog::getNewInstance(self::TYPE_ORDER, self::ACTION_STATUSCHANGE, $this->user, (int)$this->oldOrder->isCancelled->get(), (int)$this->currentOrder->isCancelled->get(), $this->oldOrder, $this->currentOrder)->save();	
       }
   }
   
   private function logOrderStatus()
   {
       if($this->oldOrder->status->get() != $this->currentOrder->status->get()) {
           OrderLog::getNewInstance(self::TYPE_ORDER, self::ACTION_STATUSCHANGE, $this->user, (int)$this->oldOrder->status->get(), (int)$this->currentOrder->status->get(), $this->oldOrder, $this->currentOrder)->save();	
       }
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