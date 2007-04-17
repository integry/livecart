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
}

?>    