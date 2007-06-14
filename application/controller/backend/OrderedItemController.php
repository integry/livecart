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
            
            if($oldShipment !== $newShipment)
            {
	            $oldShipment->loadItems();
	            $oldShipment->removeItem($item);
			    
	            $newShipment->loadItems();
	            $newShipment->addItem($item);
	            
			    $oldShipment->setRateId($oldShipment->shippingService->get()->getID());
			    $oldShipment->setAvailableRates($oldShipment->order->get()->getDeliveryZone()->getShippingRates($oldShipment));
			    $newShipment->setRateId($newShipment->shippingService->get()->getID());
			    $newShipment->setAvailableRates($newShipment->order->get()->getDeliveryZone()->getShippingRates($newShipment));
			    
			    if($newShipment->getSelectedRate())
			    {
		            $item->save();
				    
		            $oldShipment->recalculateAmounts();
		            $newShipment->recalculateAmounts();
		            	            
		            $oldShipment->save();
		            $newShipment->save();
		            
		            return new JSONResponse(array(
		                'status' => 'success', 
			            'oldShipment' => array(
			                'ID' => $oldShipment->getID(),
			                'amount' => $oldShipment->amount->get(),
			                'shippingAmount' => $oldShipment->shippingAmount->get()
		                ),
			            'newShipment' => array(
			                'ID' => $newShipment->getID(),
			                'amount' => $newShipment->amount->get(),
			                'shippingAmount' => $newShipment->shippingAmount->get()
		                )
		            ));
			    }
			    else
			    {
			        return new JSONResponse(array(
			            'status' => 'failure', 
			            'oldShipment' => array('ID' => $fromID),
			            'newShipment' => array('ID' => $toID)
		            ));
			    }
            }
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
	}
}

?>