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
     *  Used only for serialization
     */
    protected $itemIds = array();
    
	protected $availableShippingRates;   
	
	protected $selectedRateId; 
    
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
    
    public function getAvailableRates()
    {
		return $this->availableShippingRates;
	}
	
	public function setRateId($serviceId)
	{
        $this->selectedRateId = $serviceId;
    }
    
    public function getSelectedRate()
    {
        if (!$this->availableShippingRates)
        {
            return null;
        }
        
        return $this->availableShippingRates->getByServiceId($this->selectedRateId);
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
        
        if ($selected = $this->getSelectedRate())
        {
            $array['selectedRate'] = $selected->toArray();            
        }
                
        return $array;
    }
    
	public function serialize()
	{
        $this->itemIds = array();
        foreach ($this->items as $item)
        {
            $this->itemIds[] = $item->getID();
        }
        
        return parent::serialize(array('orderID'), array('itemIds', 'availableShippingRates', 'selectedRateId'));
    }      
    
    public function unserialize($serialized)
    {
        parent::unserialize($serialized);
        if ($this->itemIds)
        {
            $this->items = array();
            
            foreach ($this->itemIds as $id)    
            {
                $item = ActiveRecordModel::getInstanceById('OrderedItem', $id);
                $this->items[] = $item;
            }
            
            $this->itemIds = array();
        }
    }
    
    public function getItems()
    {
        return $this->items;
    }
}

?>