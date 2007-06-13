<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("library.*");

/**
 * ...
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 * @role order
 */
class OrderedItemController extends StoreManagementController
{
    /**
     * Delete filter from database
     * 
     * @role update
     * 
     * @return JSONResponse
     */
    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            $item = OrderedItem::getInstanceByID('OrderedItem', (int)$key); 
            $item->delete();
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

	/**
	 * @role update
	 */
	public function changeShipment()
	{ 
        if(($id = (int)$this->request->getValue("id", false)) && ($fromID = (int)$this->request->getValue("from", false)) && ($toID = (int)$this->request->getValue("to", false)))
        {
            $item = OrderedItem::getInstanceByID('OrderedItem', $id, true); 
                        
            $oldShipment = Shipment::getInstanceByID('Shipment', $fromID, true, true); 
            $newShipment = Shipment::getInstanceByID('Shipment', $toID, true, true); 
            
            echo $oldShipment->getID();
            echo " -> " . $newShipment->getID();
            echo "<br />";
            
            if($oldShipment !== $newShipment)
            {
		            $oldShipment->loadItems();
		            $newShipment->loadItems();
				    $oldShipment->setRateId($oldShipment->shippingService->get()->getID());
				    $newShipment->setRateId($newShipment->shippingService->get()->getID());
				    $oldShipment->setAvailableRates($oldShipment->order->get()->getDeliveryZone()->getShippingRates($oldShipment));
				    $newShipment->setAvailableRates($newShipment->order->get()->getDeliveryZone()->getShippingRates($newShipment));
				    
		            $newShipment->addItem($item);
		            $oldShipment->removeItem($item);
		            
		            $item->save();
				    
		            $oldShipment->recalculateAmounts();
		            $newShipment->recalculateAmounts();
		            	            
		            $oldShipment->save();
		            $newShipment->save();
	            
		            echo $newShipment->getID() . ": " . $newShipment->amount->get() ."<br />";
		            echo $newShipment->getID() . ": " . $newShipment->shippingAmount->get();
	            
            }
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
	}
}

?>