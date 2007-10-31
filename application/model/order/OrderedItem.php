<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.Shipment");

/**
 * Represents a shopping basket item (one or more instances of the same product)
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com> 
 */
class OrderedItem extends ActiveRecordModel
{
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
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("customerOrderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shipmentID", "Shipment", "ID", "Shipment", ARInteger::instance()));

		$schema->registerField(new ARField("priceCurrencyID", ARChar::instance(3)));
		$schema->registerField(new ARField("price", ARFloat::instance()));
		$schema->registerField(new ARField("count", ARFloat::instance()));
		$schema->registerField(new ARField("reservedProductCount", ARFloat::instance()));
		$schema->registerField(new ARField("dateAdded", ARDateTime::instance()));
		$schema->registerField(new ARField("isSavedForLater", ARBool::instance()));
	}
	
	/*####################  Static method implementations ####################*/		
	
	public static function getNewInstance(CustomerOrder $order, Product $product, $count = 1)	
	{
        $instance = parent::getNewInstance(__CLASS__);
        $instance->customerOrder->set($order);
        $instance->product->set($product);
        $instance->count->set($count);

        return $instance;
    }
    
	/*####################  Value retrieval and manipulation ####################*/    
    
    public function getSubTotal(Currency $currency)
    {
        return $this->getPrice($currency) * $this->count->get();    
    }
    
    public function getPrice(Currency $currency)
    {
		$itemCurrency = $this->priceCurrencyID->get() ? Currency::getInstanceById($this->priceCurrencyID->get()) : $currency;
if (!$this->product->get())	print_r($this->toArray());
		$price = $this->price->get() ? $this->price->get() : $this->product->get()->getPrice($currency->getID());
		
		return $itemCurrency->convertAmount($currency, $price);
	}
    
    public function reserve()
    {
        $product = $this->product->get();
        $product->reservedCount->set($product->reservedCount->get() + $this->reservedProductCount->get());
    }
    
    /**
     *  @todo implement
     */ 
    public function unreserve()
    {
        
    }
    
    /**
     *  Determine if the file download period hasn't expired yet
     *  
     *  @return ProductFile
     */
    public function isDownloadable(ProductFile $file)
    {
        $orderDate = $this->customerOrder->get()->dateCompleted->get();
        
        return (abs($orderDate->getDayDifference(new DateTime())) <= $file->allowDownloadDays->get()) ||
                !$file->allowDownloadDays->get();
    }    
    
  	/*####################  Saving ####################*/
    
    protected function insert()
    {
        $this->shipment->setNull();
        
        $this->priceCurrencyID->set($this->customerOrder->get()->currency->get()->getID());
        $this->price->set($this->product->get()->getPrice($this->priceCurrencyID->get()));

        return parent::insert();
    }
    
    public function save()
    {
        $ret = parent::save();        
        
        // adjust inventory
        $this->product->get()->save();
        
        return $ret;
    }
   
    protected function update()
    {                       
        if (is_null($this->shipment->get()) || !$this->shipment->get()->getID())
        {
            $this->shipment->setNull(false);
            $this->shipment->resetModifiedStatus();
        }

        if ($this->isModified())
        {
            return parent::update();
        }
        else
        {
            return false;
        }
    }
	
	/*####################  Data array transformation ####################*/    
    
    public static function transformArray($array, ARSchema $schema)
    {
        $array = parent::transformArray($array, $schema);
        
        // always use OrderedItem stored prices for presentation, rather than Product's
        // pricing data, as Product prices may change after the order is completed
        if ($array['priceCurrencyID'])
        {
            $currency = Currency::getInstanceByID($array['priceCurrencyID']);
            $array['formattedPrice'] = $currency->getFormattedPrice($array['price']);
            $array['formattedSubTotal'] = $currency->getFormattedPrice($array['price'] * $array['count']);
        }
        
        return $array;
    }
    	
	/*####################  Get related objects ####################*/	
	    
    /**
     *  @return ProductFile
     */
    public function getFileByID($id)
    {
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductFile', 'ID'), $id));
        $f->mergeCondition(new EqualsCond(new ARFieldHandle('ProductFile', 'productID'), $this->product->get()->getID()));
        $s = ActiveRecordModel::getRecordSet('ProductFile', $f);
        if (!$s->size())
        {
            return false;
        }
        else
        {
            return $s->get(0);
        }
    }
    
    public function serialize()
    {
        $this->markAsLoaded();
        return parent::serialize(array('customerOrderID', 'shipmentID', 'productID'));
    }    
}

?>