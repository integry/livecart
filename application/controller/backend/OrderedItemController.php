<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.product.*");
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
        $product = Product::getInstanceById((int)$this->request->get('productID'), true);

        if($product->isDownloadable())
        {
            $order = CustomerOrder::getInstanceByID((int)$this->request->get('orderID'), true, array('ShippingAddress' => 'UserAddress', 'Currency'));		    
	 	    $shipment = $order->getDownloadShipment();
        }
        else
        {
            $shipment = Shipment::getInstanceById('Shipment', (int)$this->request->get('shipmentID'), true, array('Order' => 'CustomerOrder', 'ShippingService', 'ShippingAddress' => 'UserAddress', 'AmountCurrency' => 'Currency'));
        }
        
        $history = new OrderHistory($shipment->order->get(), $this->user);

        
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
	        if($product->isDownloadable())
	        {
	            return new JSONResponse(false, 'failure', $this->translate('_downloadable_item_already_exists_in_this_order'));
	        }
	        else
	        {
	            $item->count->set($item->count->get() + 1);
	        }
        }
        else
        {
	        $order = $shipment->order->get();
	        $currency = $shipment->amountCurrency->get();
	        
	        $item = OrderedItem::getNewInstance($order, $product);
	        $item->count->set(1);
	        $item->priceCurrencyID->set($currency->getID());
	        $item->price->set($product->getPrice($currency->getID()));
	        
            $order->addItem($item);
            $shipment->addItem($item);
            $shipment->save();
        }
        
        $response = $this->save($item, $shipment, $existingItem ? true : false );
        
        $history->saveLog();
        
        return $response;
    }
    
    public function update()
    {
        
    }
    
    private function save(OrderedItem $item, Shipment $shipment, $existingItem = false)
    {
        $validator = $this->createOrderedItemValidator();
        if($validator->isValid())
        {
	        if($count = (int)$this->request->get('count') && !(int)$this->request->get('downloadable'))
	        {
	            $item->count->set($count);
	        }
	        
	        $shipment->loadItems();
        
	        if(!$existingItem)
	        {
	            $shipment->addItem($item);
	        }
	        
	        if($shipment->getShippingService())
	        {
	            $shipmentRates = $shipment->order->get()->getDeliveryZone()->getShippingRates($shipment);
	            $shipment->setAvailableRates($shipmentRates);
			    $shipment->setRateId($shipment->getShippingService()->getID());
	        }
	        
	        $item->save();
	        
            $shipment->recalculateAmounts();
            
            $shipment->save();
            
            return new JSONResponse(array(
	            'item' => array(
	                'ID'              => $item->getID(),
	                'Product'         => $item->product->get()->toArray(),
	                'Shipment'        => array(
							                'ID' => $shipment->getID(),
							                'amount' => (float)$shipment->amount->get(),
							                'shippingAmount' => (float)$shipment->shippingAmount->get(),
							                'taxAmount' => (float)$shipment->taxAmount->get(),
							                'total' => (float)$shipment->shippingAmount->get() + (float)$shipment->amount->get() + (float)$shipment->taxAmount->get(),
							                'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
							                'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
	                                     ),
	                'count'           => $item->count->get(),
	                'price'           => $item->price->get(),
	                'priceCurrencyID' => $item->priceCurrencyID->get(),
	                'isExisting'	  => $existingItem
	            )
            ), 'success', $this->translate('_item_has_been_successfuly_saved'));
        }
        else
        {
            return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_unable_to_update_items_quantity'));
        }
    }
	
    /**
     * Products popup
     * 
     * @role update
     */
	public function selectProduct()
	{
	    ClassLoader::import("application.model.category.Category");
	    
	    $response = new ActionResponse();	   
	    
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		
		// Do not filter out downloadable items
//	    $response->set("filters", 'filters[Product.type]=' . (int)$this->request->get('downloadable'));
	    
		$response->set("categoryList", $categoryList->toArray($this->application->getDefaultLanguageCode()));
		
        $order = CustomerOrder::getInstanceById($this->request->get('id'), true, true);
        $order->loadItems();
        
        $shipmentsArray = array();
        foreach($order->getShipments() as $shipment)
        {
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
        }
        
        $response->set("order", $order->toFlatArray());
        $response->set("shipments", $shipmentsArray);
		
		return $response;
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
        if($id = $this->request->get("id", false))
        {
            $item = OrderedItem::getInstanceByID('OrderedItem', (int)$id, true, array('Shipment', 'Order' => 'CustomerOrder', 'ShippingService', 'AmountCurrency' => 'Currency', 'ShippingAddress' => 'UserAddress', 'Product')); 
	        $shipment = $item->shipment->get();
	        $order = $shipment->order->get();
	        
	        $history = new OrderHistory($order, $this->user);
	        
	        $shipment->loadItems();
            $shipment->removeItem($item);
            $order->removeItem($item);
	        
            if($shipment->getShippingService())
            {
	            $shipmentRates = $shipment->order->get()->getDeliveryZone()->getShippingRates($shipment);
	            $shipment->setAvailableRates($shipmentRates);
			    $shipment->setRateId($shipment->getShippingService()->getID());
            }
            
            $shipment->recalculateAmounts();
            $shipment->save();
            
            $order->save();
            
            $history->saveLog();
            
            return new JSONResponse(array(
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
	            ), 
	            'success'
			);
        }
        else
        {
            return new JSONResponse(false, 'failure', '_error_removing_item_from_shipment');
        }
    }

	/**
	 * @role update
	 */
	public function changeShipment()
	{ 
        if(($id = (int)$this->request->get("id", false)) && ($fromID = (int)$this->request->get("from", false)) && ($toID = (int)$this->request->get("to", false)))
        {
            $item = OrderedItem::getInstanceByID('OrderedItem', $id, true, array('Product')); 
                    
            $oldShipment = Shipment::getInstanceByID('Shipment', $fromID, true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress', 'AmountCurrency' => 'Currency')); 
            $newShipment = Shipment::getInstanceByID('Shipment', $toID, true, array('Order' => 'CustomerOrder', 'ShippingAddress' => 'UserAddress', 'AmountCurrency' => 'Currency')); 

            $history = new OrderHistory($oldShipment->order->get(), $this->user);
            
            $zone = $oldShipment->order->get()->getDeliveryZone();
            
            if($oldShipment !== $newShipment)
            {
	            $oldShipment->loadItems();
	            $oldShipment->removeItem($item);
	            
	            $newShipment->loadItems();
	            $newShipment->addItem($item);
	            
	            
	            if($oldShipment->getShippingService())
	            {
		            $shipmentRates = $zone->getShippingRates($oldShipment);
		            $oldShipment->setAvailableRates($shipmentRates);
		            
				    $oldShipment->setRateId($oldShipment->getShippingService()->getID());
				    $oldShipment->save();
	            }
            
	            if($newShipment->getShippingService())
	            {
		            $shipmentRates = $zone->getShippingRates($newShipment);
		            $newShipment->setAvailableRates($shipmentRates);
		            
				    $newShipment->setRateId($newShipment->getShippingService()->getID());
				    $newShipment->save();
	            }
	            
	            $item->save();
			    
			    if($newShipment->getSelectedRate() || !$newShipment->getShippingService() || !is_int($newShipment->getShippingService()->getID()))
			    {
		            $item->save();
		            
		            $oldShipment->recalculateAmounts();
		            $newShipment->recalculateAmounts();
		            	            
		            $oldShipment->save();
		            $newShipment->save();
		            
		            $history->saveLog();
    
		            return new JSONResponse(
		                array(
				            'oldShipment' => array(
				                'ID' => $oldShipment->getID(),
				                'amount' => $oldShipment->amount->get(),
				                'shippingAmount' => $oldShipment->shippingAmount->get(),
				                'taxAmount' => $oldShipment->taxAmount->get(),    
				                'total' =>((float)$oldShipment->shippingAmount->get() + (float)$oldShipment->amount->get() + (float)$oldShipment->taxAmount->get()),
				                'prefix' => $oldShipment->amountCurrency->get()->pricePrefix->get(),
				                'suffix' => $oldShipment->amountCurrency->get()->priceSuffix->get()
			                ),
				            'newShipment' => array(
				                'ID' => $newShipment->getID(),
				                'amount' =>  $newShipment->amount->get(),
				                'shippingAmount' =>  $newShipment->shippingAmount->get(),
				                'taxAmount' => $newShipment->taxAmount->get(),
				                'total' => ((float)$newShipment->shippingAmount->get() + (float)$newShipment->amount->get() + (float)$newShipment->taxAmount->get()),
				                'prefix' => $newShipment->amountCurrency->get()->pricePrefix->get(),
				                'suffix' => $newShipment->amountCurrency->get()->priceSuffix->get()
			                )
			            ), 
			            'success', 
			            $this->translate('_ordered_item_was_successfully_moved')
		            );
			    }
			    else
			    {
			        return new JSONResponse(
				        array(
				            'oldShipment' => array('ID' => $fromID),
				            'newShipment' => array('ID' => $toID)
			            ), 
			            'failure', 
			            $this->translate('_this_shipping_service_has_no_available_rates')
		            );
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
        if(($id = (int)$this->request->get("id", false)) )
        {
            $count = (int)$this->request->get("count");
            $item = OrderedItem::getInstanceByID('OrderedItem', $id, true, array('Shipment', 'Order' => 'CustomerOrder', 'ShippingService', 'AmountCurrency' => 'Currency', 'ShippingAddress' => 'UserAddress', 'Product')); 
            $history = new OrderHistory($item->customerOrder->get(), $this->user);
            
            $item->count->set($count);
            
            $shipment = $item->shipment->get();
		    $shipment->loadItems();
		      
            if($shipment->getShippingService())
            {
	            $shipmentRates = $shipment->order->get()->getDeliveryZone()->getShippingRates($shipment);
	            $shipment->setAvailableRates($shipmentRates);
			    $shipment->setRateId($shipment->getShippingService()->getID());
            }
		    
	        $shipment->recalculateAmounts();
	        
		    $item->save();
		    $shipment->save();
		    
		    $history->saveLog();
		    
		    return new JSONResponse(array(
				'shipment' => array(
				    'ID' => $shipment->getID(),
				    'amount' => $shipment->amount->get(),
				    'shippingAmount' => $shipment->shippingAmount->get(),
				    'total' =>((float)$shipment->shippingAmount->get() + (float)$shipment->amount->get() + (float)$shipment->taxAmount->get()),
	                'taxAmount' => $shipment->taxAmount->get(),
				    'prefix' => $shipment->amountCurrency->get()->pricePrefix->get(),
				    'suffix' => $shipment->amountCurrency->get()->priceSuffix->get()
				 )),
				 'success',
				 $this->translate('_ordered_item_quantity_was_updated')
			 );
        }
        else
        {
	        return new JSONResponse(false, 'failure', $this->translate('_error_updating_item_quantity'));
        }
	}

}

?>