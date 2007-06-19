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
        $shipment = Shipment::getInstanceById('Shipment', (int)$this->request->getValue('shipmentID'), true, array('Order' => 'CustomerOrder', 'ShippingService', 'AmountCurrency' => 'Currency'));
        $product = Product::getInstanceById((int)$this->request->getValue('productID'), true);
        
        $existingItem = false;
        foreach($shipment->getItems() as $item)
        {
            if($item->product->get() === $product)
            {
                $existingItem = $item;
                break;
            }
        }
        
        if($existingItem)
        {
	        $item = $existingItem;
	        $item->count->set($item->count->get() + 1);
        }
        else
        {
	        $order = $shipment->order->get();
	        $currency = $shipment->amountCurrency->get();
	        
	        $item = OrderedItem::getNewInstance($order, $product);
	        $item->count->set(1);
	        $item->priceCurrencyID->set($currency->getID());
	        $item->price->set($product->getPrice($currency->getID()));
	        
            $shipment->addItem($item);
            $shipment->save();
        }
          
        return $this->save($item, $existingItem ? true : false );
    }
    
    public function update()
    {
        
    }
    
    private function save(OrderedItem $item, $existingItem = false)
    {
        $validator = $this->createOrderedItemValidator();
        if($validator->isValid())
        {
	        if($count = (int)$this->request->getValue('count'))
	        {
	            $item->count->set($count);
	        }
	        
	        $shipment = $item->shipment->get();
	        $shipment->loadItems();
	        
	        if(!$existingItem)
	        {
	            $shipment->addItem($item);
	        }
	        
	        $shipment->setRateId($shipment->shippingService->get()->getID());
		    $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
	        
	        $item->save();
            
            $shipment->recalculateAmounts();
            $shipment->save();
			    
            return new JSONResponse(array(
	            'status' => 'succsess', 
	            'item' => array(
	                'ID'              => $item->getID(),
	                'Product'         => $item->product->get()->toArray(),
	                'Shipment'        => array(
							                'ID' => $shipment->getID(),
							                'amount' => $shipment->amount->get(),
							                'shippingAmount' => $shipment->shippingAmount->get(),
							                'taxAmount' => $shipment->taxAmount->get(),    
							                'total' =>((float)$shipment->shippingAmount->get() + (float)$shipment->amount->get() + (float)$shipment->taxAmount->get()),
							                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
							                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
	                                     ),
	                'count'           => $item->count->get(),
	                'price'           => $item->price->get(),
	                'priceCurrencyID' => $item->priceCurrencyID->get(),
	                'isExisting'	  => $existingItem
	            )
            ));
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
            $item = OrderedItem::getInstanceByID('OrderedItem', (int)$id, true, array('Shipment', 'Order' => 'CustomerOrder', 'ShippingService', 'AmountCurrency' => 'Currency')); 
                        
	        $shipment = $item->shipment->get();
	        
	        $shipment->loadItems();
            $shipment->removeItem($item);
	        
	        $shipment->setRateId($shipment->shippingService->get()->getID());
		    $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
	        
            $item->delete();
            
            $shipment->recalculateAmounts();
            
            $shipment->save();
            
            return new JSONResponse(array(
	            'status' => 'succsess', 
	            'item' => array(
	                'ID'              => $item->getID(),
	                'Shipment'        => array(
							                'ID' => $shipment->getID(),
							                'amount' => $shipment->amount->get(),
							                'shippingAmount' => $shipment->shippingAmount->get(),
							                'taxAmount' => $shipment->taxAmount->get(),    
							                'total' =>((float)$shipment->shippingAmount->get() + (float)$shipment->amount->get() + (float)$shipment->taxAmount->get()),
							                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
							                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
	                                     ),
	                'count'           => $item->count->get(),
	                'price'           => $item->price->get(),
	                'priceCurrencyID' => $item->priceCurrencyID->get()
	            )
            ));
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
			                'taxAmount' => $shipment->taxAmount->get(),    
			                'total' =>((float)$oldShipment->shippingAmount->get() + (float)$oldShipment->amount->get() + (float)$oldShipment->taxAmount->get()),
			                'prefix' => $oldShipment->amountCurrency->get()->pricePrefix->get(),
			                'suffix' => $oldShipment->amountCurrency->get()->priceSuffix->get()
		                ),
			            'newShipment' => array(
			                'ID' => $newShipment->getID(),
			                'amount' =>  $newShipment->amount->get(),
			                'shippingAmount' =>  $newShipment->shippingAmount->get(),
			                'taxAmount' => $shipment->taxAmount->get(),
			                'total' => ((float)$newShipment->shippingAmount->get() + (float)$newShipment->amount->get() + (float)$newShipment->taxAmount->get()),
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

	public function changeCount()
	{   
        if(($id = (int)$this->request->getValue("id", false)) )
        {
            $count = (int)$this->request->getValue("count");
            $item = OrderedItem::getInstanceByID('OrderedItem', $id, true, array('Shipment', 'Order' => 'CustomerOrder')); 
            $item->count->set($count);
            
            $shipment = $item->shipment->get();
		    $shipment->loadItems();
		    
	        $shipment->setAvailableRates($shipment->order->get()->getDeliveryZone()->getShippingRates($shipment));
	        $shipment->setRateId($shipment->shippingService->get()->getID());
	        
	        $shipment->recalculateAmounts();
		    
		    $shipment->save();
		    
		    return new JSONResponse(array(
		        'status' => 'success',
				'shipment' => array(
				    'ID' => $shipment->getID(),
				    'amount' => $shipment->amount->get(),
				    'shippingAmount' => $shipment->shippingAmount->get(),
				    'total' =>((float)$shipment->shippingAmount->get() + (float)$shipment->amount->get() + (float)$shipment->taxAmount->get()),
	                'taxAmount' => $shipment->taxAmount->get(),
				    'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
				    'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
				 )
			));
        }
        else
        {
	        return new JSONResponse(array(
	            'status' => 'failure'
           ));
        }
	}
}

?>