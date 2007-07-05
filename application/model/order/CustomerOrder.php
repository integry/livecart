<?php

ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.delivery.ShipmentDeliveryRate");

/**
 * Represents customers order - products placed in shopping basket or wish list
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com> 
 */
class CustomerOrder extends ActiveRecordModel
{
	public $orderedItems = array();
	
	public $shipments = array();	
	
	private $removedItems = array();
	    
    private $taxes = array();
    
    const STATUS_NEW = null;
    const STATUS_BACKORDERED = 1;
    const STATUS_AWAITING_SHIPMENT = 2;
    const STATUS_SHIPPED = 3;
    const STATUS_RETURNED = 4;        
                
    /**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingAddressID", "shippingAddress", "ID", 'UserAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("billingAddressID", "billingAddress", "ID", 'UserAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("currencyID", "currency", "ID", 'Currency', ARChar::instance(3)));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateCompleted", ARDateTime::instance()));
		$schema->registerField(new ARField("totalAmount", ARFloat::instance()));
		$schema->registerField(new ARField("capturedAmount", ARFloat::instance()));
		$schema->registerField(new ARField("isFinalized", ARBool::instance()));
		$schema->registerField(new ARField("isPaid", ARBool::instance()));
		$schema->registerField(new ARField("isCancelled", ARBool::instance()));
		$schema->registerField(new ARField("status", ARInteger::instance()));
		$schema->registerField(new ARField("shipping", ARText::instance()));
	}
		
	public static function getNewInstance(User $user)	
	{
        $instance = parent::getNewInstance(__CLASS__);
		$instance->user->set($user);     
        
        return $instance;   
    }
    
    public static function getInstanceById($id, $loadData = self::LOAD_DATA, $loadReferencedRecords = false)
    {
        $instance = ActiveRecordModel::getInstanceById('CustomerOrder', $id, $loadData, $loadReferencedRecords);
        $instance->loadItems();
        return $instance;
    }
    
    /**
     * @return ARSet
     */
    public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
    {
        return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
    }
    
    public function loadItems()
    {
        $this->orderedItems = $this->getRelatedRecordSet('OrderedItem', new ARSelectFilter(), array('Product', 'Category', 'DefaultImage' => 'ProductImage'))->getData();
        $this->shipments = $this->getRelatedRecordSet('Shipment', new ARSelectFilter(), self::LOAD_REFERENCES)->getData();
        if (!$this->shipments)
        {
			$this->shipments = unserialize($this->shipping->get());
		}
	}
	
	public function loadAddresses()
	{
        if ($this->billingAddress->get())
        {
            $this->billingAddress->get()->load();               
        }

		if ($this->shippingAddress->get())
		{
            $this->shippingAddress->get()->load(); 
        }
    }
    
    public function loadAll()
    {
        $this->loadItems();
        $this->loadAddresses();
        $this->getShipments();
    }

    /**
     *	Add a product to shopping basket
     */
	public function addProduct(Product $product, $count)
    {
        if (0 >= $count)
        {
            $this->removeProduct($product);
        }
        else
        {
            if (!$product->isAvailable())
            {
                throw new ApplicationException('Product is not available (' . $product->getID() . ')');
            }
                        
			$count = $this->validateCount($product, $count);
			$this->orderedItems[] = OrderedItem::getNewInstance($this, $product, $count);
        }
        
        $this->resetShipments();
    }
    
    public function updateCount(OrderedItem $item, $count)
    {
        $item->count->set($this->validateCount($item->product->get(), $count));
	}
    
    private function validateCount(Product $product, $count)
    {
        if (round($count) != $count && !$product->isFractionalUnit->get())
        {
			$count = round($count);
		}
		
        if (0 >= $count)
        {
            $count = 0;
        }	
		
		return $count;		
	}
    
	/**
     *	Add a product to wish list
     */
	public function addToWishList(Product $product)
    {
        $item = OrderedItem::getNewInstance($this, $product, 1);
        $item->isSavedForLater->set(true);
		$this->orderedItems[] = $item;
    }
    
    /**
     *	Remove a product (all product items) from shopping basket or wish list
     */
	public function removeProduct(Product $product)
    {
        $id = $product->getID();
        
        foreach ($this->orderedItems as $key => $item)
        {
            if ($item->product->get()->getID() == $id)
            {
                $this->removeItem($item);
            }
        }    
    }

    /**
     *	Remove an item from shopping basket or wish list
     */
	public function removeItem(OrderedItem $orderedItem)
    {
        foreach ($this->orderedItems as $key => $item)
        {
            if ($item === $orderedItem)
            {
                $this->removedItems[] = $item;
                unset($this->orderedItems[$key]);
                $this->resetShipments(); 
            }
        } 
    }
    
    /**
     *	Move an item to a different order
     */
	public function moveItem(OrderedItem $orderedItem, CustomerOrder $order)
    {
        foreach ($this->orderedItems as $key => $item)
        {
            if ($item === $orderedItem)
            {
                unset($this->orderedItems[$key]);
                $order->addItem($item);
                
                $this->resetShipments();
				$order->resetShipments(); 
            }
        } 
    }
    
    /**
     *	Add new ordered item
     */
	public function addItem(OrderedItem $orderedItem)
    {
        $orderedItem->customerOrder->set($this);
        $this->orderedItems[] = $orderedItem;
    }    
    
    /**
     *  "Close" the order for modifications and fix its state
     *
     *  1) fix current product prices and total (so the total doesn't change if product prices change)
     *  2) save created shipments
     *
     *	@return CustomerOrder New order instance containing wishlist items
     */
    public function finalize(Currency $currency, $reserveProducts = null)
    {
        self::beginTransaction();
        
        $this->currency->set($currency);

        $this->loadAll();
		foreach ($this->getShipments() as $shipment)
        {
            $shipment->order->set($this);
            $shipment->save();
        }
        
        if (!is_null($reserveProducts))
        {
            $reserveProducts = !Config::getInstance()->get('DISABLE_INVENTORY');            
        }
        
        foreach ($this->getShoppingCartItems() as $item)
        {
            // reserve products if inventory is enabled
            if ($reserveProducts)
            {
                $item->reserve();
            }
            
            $item->priceCurrencyID->set($currency->getID());
            $item->price->set($item->product->get()->getPrice($currency->getID()));
            $item->save();
        }
        
        // clone billing/shipping addresses
        if ($this->shippingAddress->get())
        {
            $shippingAddress = clone $this->shippingAddress->get();
            $shippingAddress->save();
            $this->shippingAddress->set($shippingAddress);            
        }
        
        $billingAddress = clone $this->billingAddress->get();
        $billingAddress->save(); 
        $this->billingAddress->set($billingAddress);

        // move wish list items to a separate order
        $wishList = CustomerOrder::getNewInstance($this->user->get());
        foreach ($this->getWishListItems() as $item)
        {
            $wishList->addItem($item);
        }
        $wishList->save();
                
        $this->isFinalized->set(true);
        $this->dateCompleted->set(new ARSerializableDateTime());
		$this->save();

        self::commit();
        
    	return $wishList;
    }
    
    public function addCapturedAmount($amount)
    {
        $this->capturedAmount->set($this->capturedAmount->get() + $amount);
    }
    
    public function save($deleteEmpty = true)
    {
        // remove zero-count items
        foreach ($this->orderedItems as $item)
        {
            if (!$item->count->get())
            {
				$this->removeItem($item);
			}
		}

        // delete removed items
        if ($this->removedItems)
        {
            foreach ($this->removedItems as $item)
            {
                $item->delete();
                $isModified = true;
            }      

            $this->removedItems = array();
            $this->resetShipments();             
        }
        
        if ($this->orderedItems)
        {
            if(!$this->currency->get())
            {
                $this->currency->set($this->getStore()->getDefaultCurrency());
            }
            
	        $this->totalAmount->set($this->getTotal($this->currency->get()));
            
            parent::save();
            
            $isModified = false;
            foreach ($this->orderedItems as $item)
            {
				if ($item->isModified())
				{                        
                    if ($item->save())
                    {
                        $isModified = true;                            
                    }
                }
                
                $item->markAsLoaded();
            }    
                        
            // reorder shipments when cart items are modified
            if ($isModified)
            {
                $this->resetShipments(); 
            }                      
        
	        $this->shipping->set(serialize($this->shipments));		
		}
        
		parent::save();
		
        if (!$this->isFinalized->get() && !$this->orderedItems)
        {
            $this->delete();
        }
    }    
    
    /**
     *  Merge OrderedItem instances of the same product into one instance
     */
    public function mergeItems()
    {
		$byProduct = array();
		
		foreach ($this->orderedItems as $item)
		{
			$byProduct[$item->product->get()->getID()][$item->isSavedForLater->get()][] = $item;
		}
		
		foreach ($byProduct as $productID => $itemsByStatus)
		{
			foreach ($itemsByStatus as $status => $items)
			{
				if (count($items) > 1)
				{
					$mainItem = array_shift($items);
					$count = $mainItem->count->get();
					
					foreach ($items as $item)
					{
						$count += $item->count->get();
						$this->removeItem($item);
					}
					
					$mainItem->count->set($count);
				}				
			}	
		}        
    }
    
    public function getShoppingCartItems()
    {
		$items = array();

        foreach ($this->orderedItems as $item)
		{
			if (!$item->isSavedForLater->get())
			{
				$items[] = $item;
			}
		}        
		
		return $items;
    }
    
    public function getWishListItems()
    {
		$items = array();
        foreach ($this->orderedItems as $item)
		{
			if ($item->isSavedForLater->get())
			{
				$items[] = $item;
			}
		}        
		
		return $items;
    }
    
	public function getOrderedItems()
	{
		return $this->orderedItems;
	}
    
    public function getShoppingCartItemCount()
	{
		$count = 0;

		foreach ($this->getShoppingCartItems() as $item)
		{
        	$count += $item->count->get();
		}
		
		return $count;
	}    
    
    public function getWishListItemCount()
	{
		return count($this->getWishListItems());
	}    

	public function getItemsByProduct(Product $product)
	{
		$items = array();
        foreach ($this->orderedItems as $item)
		{
			if ($item->product->get()->getID() == $product->getID())
			{
				$items[] = $item;
			}
		}        
		
		return $items;		
	}

    /**
     *  Return OrderedItem instance by ID
     */
	public function getItemByID($id)
	{
        foreach ($this->orderedItems as $item)
		{
			if ($item->getID() == $id)
			{
				return $item;
			}
		}			
	}

	public function getSubTotal(Currency $currency)
	{
        $subTotal = 0;
        foreach ($this->orderedItems as $item)
        {
            if (!$item->isSavedForLater->get())
            {
				$subTotal += $item->getSubTotal($currency);				
			}
        }
        
        return $subTotal;	
	}

    /**
     *  Loads ordered item/product info from database
     */
    public function loadItemData()
    {
        $productIDs = array();
        
        foreach ($this->orderedItems as $item)
        {
			$productIDs[] = $item->product->get()->getID();
		}
		
		$products = ActiveRecordModel::getInstanceArray('Product', $productIDs);
		
        foreach ($this->orderedItems as $item)
        {
			$id = $item->product->get()->getID();
			
			if (isset($products[$id]))
			{
				$item->product->set($products[$id]);
			}
			else
			{
				$this->removeProduct($item->product->get());
			}
		}		
    }
    
    /**
     *  Separate items into shipments (if any item needs to be shipped separately)
     *
     *  @return Shipment[]
     */
    public function getShipments()
    {
        if ($this->isFinalized->get())
        {
            $this->loadItems();
            
            $filter = new ARSelectFilter(new EqualsCond(new ARFieldHandle('Shipment', 'orderID'), $this->getID()));
            $filter->setOrder(new ARFieldHandle('Shipment', 'status'));
            
            $this->shipments = $this->getRelatedRecordSet('Shipment', $filter, array('ShippingService')); 
                        
            foreach($this->shipments as $shipment)
            {
                $shipment->loadItems();
            }
            
            // get downloadable items
            foreach ($this->getShoppingCartItems() as $item)
            {
                if ($item->product->get()->isDownloadable())
                {
                    if (!isset($downloadable))
                    {
                        $downloadable = Shipment::getNewInstance($this);
                        $this->shipments->add($downloadable);
                    }

                    $downloadable->addItem($item);
                }
            }            
        }
        else
        {
            if (!$this->shipments)
            {
                ClassLoader::import("application.model.order.Shipment");

                $this->shipments = new ARSet();
                
                foreach ($this->getShoppingCartItems() as $item)
                {
                    if ($item->product->get()->isDownloadable())
                    {
                        if (!isset($downloadable))
                        {
                            $downloadable = Shipment::getNewInstance($this);                            
                        }
                        
                        $downloadable->addItem($item);
                    }
                    else if ($item->product->get()->isSeparateShipment->get())
                    {
                        $shipment = Shipment::getNewInstance($this);
                        $shipment->addItem($item);
                        $this->shipments->add($shipment);
                    }
                    else
                    {
                        if (!isset($main))
                        {
                            $main = Shipment::getNewInstance($this);                            
                        }
                        
                        $main->addItem($item);
                    }
                }   
                
                if (isset($main))
                {
                    $this->shipments->unshift($main); 
                }                   

                if (isset($downloadable))
                {
                    $this->shipments->unshift($downloadable);
                }                   
            }                    
        }

        return $this->shipments;
    }
	
	public function getDeliveryZone()
	{
        ClassLoader::import("application.model.delivery.DeliveryZone");
        
        if ($this->isShippingRequired())
        {
            return DeliveryZone::getZoneByAddress($this->shippingAddress->get()); 
        }
        else
        {
            return DeliveryZone::getDefaultZoneInstance();   
        }
    }
	
	/**
	 * No shipping is required for orders consisting of downloadable items only
	 */
    public function isShippingRequired()
	{
        foreach ($this->getShoppingCartItems() as $item)
        {
            if (!$item->product->get()->isDownloadable())
            {
                return true;
            }
        }
        
        return false;
    }
	
	public function isShippingSelected()
	{
        $selected = true;
        
        foreach ($this->shipments as $shipment)
        {
            if (!$shipment->getSelectedRate())
            {
                $selected = false;
            }    
        }
        
        return $selected;
    }
	
	/**
	 *	Get total amount for order, including shipping costs
	 */
	public function getTotal(Currency $currency)
	{
        $total = 0;
        $id = $currency->getID();
        
		if ($this->shipments)
		{
    		$this->taxes[$id] = array();
            foreach ($this->shipments as $shipment)
    		{
                $total += $shipment->getSubTotal($currency);
                
	            if ($rate = $shipment->getSelectedRate())
	            {
	                $amount = $rate->getCostAmount();
	                $curr = Currency::getInstanceById($rate->getCostCurrency());
	                
	                $total += $currency->convertAmount($curr, $amount);
	            }

                foreach ($shipment->getTaxes() as $tax)
                {
                    $taxId = $tax->taxRate->get()->tax->get()->getID();
                    if (!isset($this->taxes[$id][$taxId]))
                    {
                        $this->taxes[$id][$taxId] = 0;    
                    }    
                    
                    $this->taxes[$id][$taxId] += $tax->getAmountByCurrency($currency);
                }
            }
		}
		else
		{
            $id = $currency->getID();
            foreach ($this->getShoppingCartItems() as $item)
            {
                $total += ($item->product->get()->getPrice($id) * $item->count->get());
            }            
        }		        
    
        return round($total, 2);
    }
		
	public function isBackordered()
	{
        return $this->status->get() > self::STATUS_BACKORDERED; 
    }

	public function isAwaitingShipment()
	{
        return $this->status->get() > self::STATUS_AWAITING_SHIPMENT; 
    }

	public function isShipped()
	{
        return $this->status->get() > self::STATUS_SHIPPED; 
    }
    
	public function isReturned()
	{
        return $this->status->get() > self::STATUS_RETURNED; 
    }    
    		
	/**
	 *	Creates an array representation of the shopping cart
	 */
	public function toArray($options = array())
	{
		$array = parent::toArray();
		
		$array['cartItems']	= array();
		$array['wishListItems']	= array();
				
		if(is_array($this->orderedItems))
		{
			foreach ($this->orderedItems as $item)
			{
				if ($item->isSavedForLater->get())
				{
					$array['wishListItems'][] = $item->toArray();
				}
				else
				{
					$array['cartItems'][] = $item->toArray();
				}
			}
		}			
	
		$array['basketCount'] = $this->getShoppingCartItemCount();
		$array['wishListCount'] = $this->getWishListItemCount();
		
		// shipments
		$array['shipments'] = array();
		if ($this->shipments)
		{
	        foreach ($this->shipments as $shipment)
			{
	            $array['shipments'][] = $shipment->toArray();
	        }
		}
		
		// taxes
		$array['taxes'] = array();
		foreach ($this->taxes as $currencyId => $taxes)
		{
            $array['taxes'][$currencyId] = array();
            $currency = Currency::getInstanceById($currencyId);
            
            foreach ($taxes as $taxId => $amount)
            {
                $array['taxes'][$currencyId][$taxId] = Tax::getInstanceById($taxId)->toArray();
                $array['taxes'][$currencyId][$taxId]['formattedAmount'] = $currency->getFormattedPrice($amount);
            }
        }
		
		// total for all currencies
		$total = array();
		$currencies = $this->getStore()->getCurrencySet();

		if (is_array($currencies))
		{
	        foreach ($currencies as $id => $currency)
	        {
	            if ($this->isFinalized->get())
	            {
                    $total[$id] = $currency->convertAmount($this->currency->get(), $array['totalAmount']);
                }
                else
                {
                    $total[$id] = $this->getTotal($currency);
                }
	        }
		}
        			
		$array['total'] = $total;
		
		$array['formattedTotal'] = array();
		if (is_array($array['total']))
		{
	        foreach ($array['total'] as $id => $amount)
			{
	            $array['formattedTotal'][$id] = $currencies[$id]->getFormattedPrice($amount);   
	        }
		}
        
        // order type
        $array['isShippingRequired'] = (int)$this->isShippingRequired();
        
        // status
        $array['isReturned'] = (int)$this->isReturned();;
        $array['isShipped'] = (int)$this->isShipped();
        $array['isAwaitingShipment'] = (int)$this->isAwaitingShipment();
        $array['isBackordered'] = (int)$this->isBackordered();

		// payments
		if (isset($options['payments']))
		{
			$currency = $this->currency->get();
			$array['amountPaid'] = $this->getPaidAmount();
			
			$array['amountNotCaptured'] = $array['amountPaid'] - $array['capturedAmount'];
			if ($array['amountNotCaptured'] < 0)
			{
				$array['amountNotCaptured'] = 0;	
			}
			
			$array['amountDue'] = $array['total'][$currency->getID()] - $array['amountPaid'];
			if ($array['amountDue'] < 0)
			{
				$array['amountDue'] = 0;	
			}
						
			foreach (array('amountPaid', 'amountNotCaptured', 'amountDue') as $key)
			{
				$array['formatted_' . $key] = $currency->getFormattedPrice($array[$key]);
			}
		}

		return $array;
	}
	
	/**
	 *	Merge two orders into one
	 */
	public function merge(CustomerOrder $order)
	{
		foreach ($order->getOrderedItems() as $item)
		{
			$order->moveItem($item, $this);
		}
		
		$this->mergeItems();
	}
	
	public function serialize()
	{
        return parent::serialize(array('userID'), array('orderedItems', 'shipments'));        
    }
    
    public function unserialize($serialized)
    {
        parent::unserialize($serialized);
        
        // load products
        $productIds = array();
        foreach ($this->orderedItems as $item)
        {
            $productIds[] = $item->product->get()->getID();
        }
        
        $products = ActiveRecordModel::getInstanceArray('Product', $productIds, Product::LOAD_REFERENCES);
        
        // load product prices
        $set = new ARSet();
        foreach ($products as $product)
        {
            $set->add($product);
        }
        
        ProductPrice::loadPricesForRecordSet($set);
    }
    
    public function resetShipments()
    {
        $this->shipments = array();
    }    
    
    public function getDownloadShipment()
    {
 	    foreach($this->getShipments() as $shipment)
 	    {
 	        if(!$shipment->isShippable()) return $shipment;
 	    }
 	    
 	    $shipment = Shipment::getNewInstance($this);
	    $shipment->amountCurrency->set($this->currency->get());
   		$shipment->save();
   	
   		return $shipment;
    }

    /**
     *  Return all transactions that are related to this order
     */
    public function getTransactions(ARSelectFilter $filter = null)
    {
        ClassLoader::import('application.model.order.Transaction');
        if (is_null($filter))
        {
			$filter = new ARSelectFilter();			
		}
        $filter->setOrder(new ARFieldHandle('Transaction', 'ID'), 'ASC');
        return $this->getRelatedRecordSet('Transaction', $filter);
    }
    
    public function getPaidAmount()
    {
		$filter = new ARSelectFilter(new InCond(new ARFieldHandle('Transaction', 'type'), array(Transaction::TYPE_AUTH, Transaction::TYPE_SALE)));
		$filter->mergeCondition(new NotEqualsCond(new ARFieldHandle('Transaction', 'isVoided'), true));
		
		$transactions = $this->getTransactions($filter);
		$paid = 0;
		foreach ($transactions as $transaction)
		{
			$paid += $transaction->amount->get();
		}
		
		return $paid;
	}
	
	public function getNotes()
	{
        $f = new ARSelectFilter();
        $f->setOrder(new ARFieldHandle('OrderNote', 'ID'), 'DESC');
        return $this->getRelatedRecordSet('OrderNote', $f, OrderNote::LOAD_REFERENCES);
    }
}
	
?>