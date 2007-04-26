<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.system.SessionSyncable");
ClassLoader::import("application.model.delivery.ShipmentDeliveryRate");


/**
 * Represents customers order - products placed in shopping basket
 *
 * @package application.model.order
 */
class CustomerOrder extends ActiveRecordModel implements SessionSyncable
{
	public $orderedItems = array();
	
	public $shipments = array();	
	
	private $removedItems = array();
	    
    private static $instance = null;
    
    private $isSyncedToSession = false;
                
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

		$schema->registerField(new ARField("dateCreated", ARTimeStamp::instance()));
		$schema->registerField(new ARField("dateCompleted", ARTimeStamp::instance()));
		$schema->registerField(new ARField("totalAmount", ARFloat::instance()));
		$schema->registerField(new ARField("capturedAmount", ARFloat::instance()));
		$schema->registerField(new ARField("isPaid", ARBool::instance()));
		$schema->registerField(new ARField("isDelivered", ARBool::instance()));
		$schema->registerField(new ARField("isReturned", ARBool::instance()));
		$schema->registerField(new ARField("isCancelled", ARBool::instance()));        		
	}
		
	public static function getNewInstance(User $user)	
	{
        $instance = parent::getNewInstance(__CLASS__);
		$instance->user->set($user);     
        
        return $instance;   
    }
    
    public static function getInstanceById($id)
    {
        $f = new ARSelectFilter();
        
        $instance = ActiveRecordModel::getInstanceById('CustomerOrder', $id, self::LOAD_DATA, self::LOAD_REFERENCES);
        $instance->orderedItems = $instance->getRelatedRecordSet('OrderedItem', new ARSelectFilter(), self::LOAD_REFERENCES);
        $instance->shipments = $instance->getRelatedRecordSet('Shipment', new ARSelectFilter());
        
        return $instance;
    }
    
    /**
     *	Get instance from session
     */
	public static function getInstance()
	{
        if (!self::$instance)
        {
            $instance = Session::getInstance()->getObject('CustomerOrder');
                
            if (!$instance)
            {
                $instance = self::getNewInstance(User::getCurrentUser());
            }    
            else
            {
                $instance->isSyncedToSession = true;
            }
            
            self::$instance = $instance;
        }
                
        return self::$instance;
    }
    
    /**
     *	Add a product to shopping basket
     */
	public function addProduct(Product $product, $count)
    {
        if ($count < 0)
        {
            throw new ApplicationException('Invalid product count (' . $count . ')');
        }
        
        if (0 == $count)
        {
            $this->removeProduct($product);
        }
        else
        {
            if (!$product->isAvailable())
            {
                throw new ApplicationException('Product is not available (' . $product->getID() . ')');
            }
                        
            $this->orderedItems[] = OrderedItem::getNewInstance($this, $product, $count);
        }
        
        $this->resetShipments();
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
     *  "Close" the order for modifications and fix its state
     *
     *  1) fix current product prices and total (so the total doesn't change if product prices change)
     *  2) save created shipments
     */
    public function finalize(Currency $currency, $reserveProducts = null)
    {
        foreach ($this->getShipments() as $shipment)
        {
            $shipment->order->set($this);
            $shipment->save();
        }
        
        if (!is_null($reserveProducts))
        {
            $reserveProducts = !Config::getInstance()->getValue('DISABLE_INVENTORY');            
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
        
        $this->totalAmount->set($this->getTotal($currency));
        $this->currency->set($currency);
        
        $this->save();
    }
    
    public function save()
    {
        // update order status if captured amount has changed
        if ($this->capturedAmount->isModified() && ($this->capturedAmount->get() == $this->totalAmount->get()))
        {
            $this->isPaid->set(true);
        }
        
        // remove zero-count items
        foreach ($this->orderedItems as $item)
        {
            if (!$item->count->get())
            {
				$this->removeItem($item);
			}
		}

        if ($this->orderedItems || $this->removedItems)
        {
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
    
            foreach ($this->removedItems as $item)
            {
                $item->delete();
                $isModified = true;
            }      

            $this->removedItems = array();
                    
            // reorder shipments when cart items are modified
            if ($isModified)
            {
                $this->resetShipments();   
            }                      
        }        
        
        if ($this->isSyncedToSession)
        {
            $this->syncToSession();
        }
    }    
    
    /**
     *	Save to database and put in session
     */
	public function saveToSession()
    {
        $this->save();
        $this->syncToSession();
    }
    
    public function syncToSession()
    {
        $this->isSyncedToSession = true;
		Session::getInstance()->setValue('CustomerOrder', $this);        
    }
    
    public function isSyncedToSession()
    {
        return $this->isSyncedToSession;
    }
    
    /**
     *  @todo implement
     */
    public function refresh()
    {
        
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

	public function getOrderedItems()
	{
		return $this->orderedItems;
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
        if (!$this->shipments)
        {
            ClassLoader::import("application.model.order.Shipment");
    
            $main = Shipment::getNewInstance($this);
            $this->shipments = new ARSet();
            
            foreach ($this->getOrderedItems() as $item)
            {
                if ($item->product->get()->isSeparateShipment->get())
                {
                    $shipment = Shipment::getNewInstance($this);
                    $shipment->addItem($item);
                    $this->shipments->add($shipment);
                }
                else
                {
                    $main->addItem($item);
                }
            }   
            
            $this->shipments->unshift($main);
        }

        return $this->shipments;
    }
	
	public function getDeliveryZone()
	{
        ClassLoader::import("application.model.delivery.DeliveryZone");
        return DeliveryZone::getZoneByAddress($this->shippingAddress->get());            
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
        
        // product price totals
        foreach ($this->getShoppingCartItems() as $item)
        {
            $total += ($item->product->get()->getPrice($id) * $item->count->get());
        }
		
		// shipping cost totals
        foreach ($this->shipments as $shipment)
		{
            if ($rate = $shipment->getSelectedRate())
            {
                $amount = $rate->getCostAmount();
                $curr = Currency::getInstanceById($rate->getCostCurrency());
                
                $total += $currency->convertAmount($curr, $amount);
            }
        }
        
        return $total;
    }
		
	/**
	 *	Creates an array representation of the shopping cart
	 */
	public function toArray()
	{
		$array = parent::toArray();
		
		if (is_array($array))
		{
			$array['cartItems']	= array();
			$array['wishListItems']	= array();
					
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
		
			$array['basketCount'] = $this->getShoppingCartItemCount();
			$array['wishListCount'] = $this->getWishListItemCount();
			
			// shipments
			$array['shipments'] = array();
            foreach ($this->shipments as $shipment)
			{
                $array['shipments'][] = $shipment->toArray();
            }
			
			// total for all currencies
			$total = array();
			$currencies = Store::getInstance()->getCurrencySet();            
            foreach ($currencies as $id => $currency)
            {
                $total[$id] = $this->getTotal($currency);
            }
            			
			$array['total'] = $total;
			
			$array['formattedTotal'] = array();
            foreach ($array['total'] as $id => $amount)
			{
                $array['formattedTotal'][$id] = $currencies[$id]->getFormattedPrice($amount);   
            }
		}	
		
		return $array;
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
}
	
?>