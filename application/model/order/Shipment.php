<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");

/**
 * Represents a collection of ordered items that are shipped in the same package
 *
 * @package application.model.order
 */
class Shipment extends ActiveRecordModel
{
    protected $items = array();
    
	protected $availableShippingRates = null;    
    
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
		$schema->registerField(new ARForeignKeyField("orderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));

		$schema->registerField(new ARField("status", ARInteger::instance(2)));
	}       
	
	public static function getNewInstance(CustomerOrder $order)
	{
        $instance = parent::getNewInstance(__class__);
        $instance->order->set($order);
        return $instance;
    }
	
	public function addItem(OrderedItem $item)
	{
        $this->items[] = $item;
        $item->shipment->set($this);
    }
    
    public function getChargeableWeight(DeliveryZone $zone)
    {
        $weight = 0;
        
        foreach ($this->items as $item)
        {
            if (!$item->product->get()->isFreeShipping->get() || !$zone->isFreeShipping->get())
            {
                $weight += $item->product->get()->shippingWeight->get();
            }
        }   
        
        return $weight;
    }
    
    public function setAvailableRates(ShippingRateSet $rates)
    {
        $this->availableShippingRates = $rates;
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        
        $items = array();
        $subTotal = array();
        $currencies = Store::getInstance()->getCurrencySet();
        
        foreach ($this->items as $item)
        {
            $items[] = $item->toArray();
            
            foreach ($currencies as $id => $currency)
            {
                if (!isset($subTotal[$id]))
                {
                    $subTotal[$id] = 0;
                }                
                $subTotal[$id] += $item->getSubTotal($currency);
            }
        }        
        
        $formattedSubTotal = array();
        foreach ($subTotal as $id => $price)
        {
            $formattedSubTotal[$id] = Currency::getInstanceById($id)->getFormattedPrice($price);
        }
        
        $array['items'] = $items;      
        $array['subTotal'] = $subTotal;
        $array['formattedSubTotal'] = $formattedSubTotal;
                
        return $array;
    }
    
	public function serialize()
	{
        return parent::serialize(array('orderID'), array('items'));
    }    
}

?>