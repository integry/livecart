<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("library.*");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");

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
    public function create()
    {
        $order = CustomerOrder::getInstanceById((int)$this->request->getValue('orderID'));
        $product = Product::getInstanceById((int)$this->request->getValue('productID'));
        $item = OrderedItem::getNewInstance($order, $product);
        $item->count->set(1);
        
        return $this->save($item);
    }
    
    public function update()
    {
        
    }
    
    private function save(OrderedItem $item)
    {
        $validator = $this->createOrderedItemValidator();
        if($validator->isValid())
        {
	        if($count = (int)$this->request->getValue('count'))
	        {
	            $item->count->set($count);
	        }
	        
	        $item->save();
	        
            return new JSONResponse(array('status' => 'succsess', 'item' => $item->toArray()));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
        }
    }
    
    /**
     * @return RequestValidator
     */
    private function createOrderedItemValidator()
    {
		$validator = new RequestValidator('orderedItem', $this->request);
		$validator->addCheck('productID', new MinValueCheck('_err_invalid_product', 0));
		$validator->addCheck('orderID', new MinValueCheck('_err_invalid_customer_order', 0));
		$validator->addCheck('count', new MinValueCheck('_err_count_should_be_more_than_zero', 0));
		
		return $validator;
    }
    
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
			                'shippingAmount' => $oldShipment->shippingAmount->get(),
			                'totalAmount' =>((float)$oldShipment->shippingAmount->get() + (float)$oldShipment->amount->get()),
			                'prefix' => $oldShipment->amountCurrency->get()->pricePrefix->get(),
			                'suffix' => $oldShipment->amountCurrency->get()->priceSuffix->get()
		                ),
			            'newShipment' => array(
			                'ID' => $newShipment->getID(),
			                'amount' =>  $newShipment->amount->get(),
			                'shippingAmount' =>  $newShipment->shippingAmount->get(),
			                'totalAmount' => ((float)$newShipment->shippingAmount->get() + (float)$newShipment->amount->get()),
			                'prefix' => $newShipment->amountCurrency->get()->pricePrefix->get(),
			                'suffix' => $newShipment->amountCurrency->get()->priceSuffix->get()
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