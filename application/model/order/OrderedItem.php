<?php

ClassLoader::import("application.model.product.Product");

/**
 * Represents a shopping basket item (one or more instances of the same product)
 *
 * @package application.model.order
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
		$schema->registerField(new ARField("dateAdded", ARTimeStamp::instance()));
		$schema->registerField(new ARField("isSavedForLater", ARBool::instance()));
	}
	
	public static function getNewInstance(CustomerOrder $order, Product $product, $count = 1)	
	{
        $instance = parent::getNewInstance(__CLASS__);
        $instance->customerOrder->set($order);
        $instance->product->set($product);
        $instance->count->set($count);

        return $instance;
    }
    
    public function getSubTotal(Currency $currency)
    {
        $itemPrice = $this->product->get()->getPrice($currency->getID());
        return $itemPrice * $this->count->get();    
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
    
    public function serialize()
    {
        $this->markAsLoaded();
        return parent::serialize(array('customerOrderID', 'shipmentID', 'productID'));
    }
    
    public function save()
    {
        parent::save();
        
        $this->product->get()->save();
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
    
    public static function transformArray($array, $className = __CLASS__)
    {
        $array = parent::transformArray($array, $className);
        $subTotal = array();
        foreach ($array['Product']['calculated'] as $currency => $price)
        {
            $subTotal[$currency] = $price * $array['count'];
        }        
        
        $array['subTotal'] = $subTotal;
        
        $formattedSubTotal = array();
        foreach ($subTotal as $currency => $price)
        {
            $formattedSubTotal[$currency] = Currency::getInstanceByID($currency)->getFormattedPrice($price);
        }
        
        $array['formattedSubTotal'] = $formattedSubTotal;
        
        return $array;
    }
}    
?>