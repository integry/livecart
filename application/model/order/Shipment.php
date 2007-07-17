<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.ShipmentTax");

/**
 * Represents a collection of ordered items that are shipped in the same package
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>  
 */
class Shipment extends ActiveRecordModel
{
    public $items = array();
    
    /**
     *  Used only for serialization
     */
    protected $itemIds = array();
    
	protected $availableShippingRates;   
	
	protected $selectedRateId; 
    	
    const STATUS_NEW = 0;
    const STATUS_PENDING = 1;
    const STATUS_AWAITING = 2;
    const STATUS_SHIPPED = 3;
    
    const WITHOUT_TAXES = false;
    
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
		$schema->registerField(new ARForeignKeyField("amountCurrencyID", "Currency", "ID", "Currency", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingServiceID", "ShippingService", "ID", "ShippingService", ARInteger::instance()));		

		$schema->registerField(new ARField("trackingCode", ARVarchar::instance(100)));
		$schema->registerField(new ARField("dateShipped", ARDateTime::instance()));
		$schema->registerField(new ARField("amount", ARFloat::instance()));
		$schema->registerField(new ARField("taxAmount", ARFloat::instance()));
		$schema->registerField(new ARField("shippingAmount", ARFloat::instance()));
		$schema->registerField(new ARField("status", ARInteger::instance(2)));
		$schema->registerField(new ARField("shippingServiceData", ARText::instance(50)));
	}       
	
	public function load($loadReferencedRecords = false)
	{
	    parent::load($loadReferencedRecords);
	}
	
	public function loadItems()
	{
	    if(empty($this->items))
	    {
		    $filter = new ARSelectFilter();
			$filter->setCondition(new EqualsCond(new ARFieldHandle('OrderedItem', 'shipmentID'), $this->getID()));
	    
			foreach(OrderedItem::getRecordSet('OrderedItem', $filter, array('Product', 'Category', 'DefaultImage' => 'ProductImage')) as $item)
			{
			    $this->items[] = $item;
			}
	    }
	}
	
	public static function getNewInstance(CustomerOrder $order)
	{
        $instance = parent::getNewInstance(__class__);
        $instance->order->set($order);
        return $instance;
    }
	
	public function addItem(OrderedItem $item)
	{
	    foreach($this->items as $key => $shipmentItem)
        {
            if($shipmentItem === $item)
            {
				return;
            }
        }
        $this->items[] = $item;
        $item->shipment->set($this);
    }
	
	public function removeItem(OrderedItem $item)
	{
        foreach($this->items as $key => $shipmentItem)
        {
            if($shipmentItem === $item)
            {
				unset($this->items[$key]);
				$item->shipment->setNull( );
				break;
            }
        }
        
    }
    
    public function getChargeableWeight(DeliveryZone $zone = null)
    {
        $weight = 0;
        
        if (is_null($zone))
        {
            $zone = $this->order->get()->getDeliveryZone();   
        }
        
        foreach ($this->items as $item)
        {
            if (!$item->product->get()->isFreeShipping->get() || !$zone->isFreeShipping->get())
            {
                $weight += $item->product->get()->shippingWeight->get();
            }
            
        }   
        
        return $weight;
    }
    
    public function getChargeableItemCount(DeliveryZone $zone)
    {
        $count = 0;
        
        foreach ($this->items as $item)
        {
            if (!$item->product->get()->isFreeShipping->get() || !$zone->isFreeShipping->get())
            {
                $count += $item->count->get();
            }
        }   
        
        return $count;
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
    
    public function getTaxes()
    {
        // no taxes are calculated for downloadable products
        if (!$this->isShippable())
        {
            return new ARSet();
        }
        
        if (!$this->taxes)
        {
            if ($this->isLoaded())
            {
                $this->taxes = $this->getRelatedRecordSet('ShipmentTax', new ARSelectFilter(), array('Tax', 'TaxRate'));
            }
            else
            {
                $zone = $this->order->get()->getDeliveryZone();
                $rates = $zone->getTaxRates(DeliveryZone::ENABLED_TAXES);
                
                $this->taxes = new ARSet();
                
                foreach ($rates as $rate)
                {
                    $this->taxes->unshift(ShipmentTax::getNewInstance($rate, $this));
                }                
            }        
        }

        return $this->taxes;
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        
        // ordered items
        $items = array();       
        foreach ($this->items as $item)
        {            
            $items[] = $item->toArray();
        }        
        $array['items'] = $items;      
        
        // subtotal
        $currencies = self::getApplication()->getCurrencySet();
        $subTotal = array();
        foreach ($currencies as $id => $currency)
        {
            $subTotal[$id] = $this->getSubTotal($currency);
        }
        $array['subTotal'] = $subTotal;
               
        // formatted subtotal
        $formattedSubTotal = array();
        foreach ($subTotal as $id => $price)
        {
            $formattedSubTotal[$id] = Currency::getInstanceById($id)->getFormattedPrice($price);
        }        
        $array['formattedSubTotal'] = $formattedSubTotal;
        
        // selected shipping rate
        if ($selected = $this->getSelectedRate())
        {
            $selected->setApplication($this->getApplication());
            $array['selectedRate'] = $selected->toArray();    
            $array['ShippingService'] = $array['selectedRate']['ShippingService'];
        }
        
        // shipping rate for a saved shipment
        if (!isset($array['selectedRate']) && isset($array['shippingAmount']))
        {
            $currency = Currency::getInstanceByID($array['AmountCurrency']['ID']);
            $array['selectedRate']['formattedPrice'] = array();
            foreach ($currencies as $id => $currency)
            {
                $rate = $currency->convertAmount($currency, $array['shippingAmount']);
                $array['selectedRate']['formattedPrice'][$id] = Currency::getInstanceById($id)->getFormattedPrice($rate);
            }
        }
        
        // taxes
        $array['taxes'] = $this->getTaxes()->toArray();

        // consists of downloadable files only?
        $array['isShippable'] = $this->isShippable();
                
        return $array;
    }
    
    public function getShippingService()
    {
        if($this->shippingService->get())
        {
            return $this->shippingService->get();
        }
        else if($this->shippingServiceData->get())
        {
            $rate = unserialize($this->shippingServiceData->get());
            return ShippingService::getInstanceByID($rate->getServiceID());
        }
        else
        {
            return null;
        }
    }
    
    public function getSubTotal(Currency $currency, $applyTaxes = true)
    {
        $subTotal = 0;
        foreach ($this->items as $item)
        {            
            $subTotal += $item->getSubTotal($currency);
        }            
        
        if ($applyTaxes)
        {
            $subTotal = $this->applyTaxesToAmount($subTotal);
        }
        
        return $subTotal;    
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

    protected function update()
    {
        $rate = $this->getSelectedRate();
        if($rate)
        {
	        $serviceId = $rate->getServiceID();
	        if (is_numeric($serviceId))
	        {
	            $this->shippingService->set(ShippingService::getInstanceByID($serviceId));
	        }
	        else
	        {
	            $this->shippingService->set(null);
	            $this->shippingServiceData->set(serialize($rate));
	        }
        }

        // reset amounts...
        $this->amount->set(0);
        $this->shippingAmount->set(0);
        $this->taxAmount->set(0);
                
        // ... and recalculated them
        $this->recalculateAmounts();
        
        $this->status->set(self::STATUS_NEW);
        
        $ret = parent::update();
        
        // save ordered items
        foreach ($this->items as $item)
        {
            $item->shipment->set($this);
            $item->save();
        }
        
        // save taxes
        foreach ($this->getTaxes() as $tax)
        {
            $tax->save();
        }
        
        return $ret;
    }
    
    public function isShippable()
    {
        foreach ($this->items as $key => $value)
        {
            if ($value->product->get()->isDownloadable())
            {
                return false;
            }   
        }
        
        return true;
    }
    
    public function save()
    {
        // make sure the shipment doesn't consist of downloadable files only
        if (!$this->isShippable())
        {
            return false;
        }

        return parent::save();
    }
    
    protected function insert()
    {              
        // set shipping data
        $rate = $this->getSelectedRate();
        
        if($rate)
        {        
	        $serviceId = $rate->getServiceID();
	        if (is_numeric($serviceId))
	        {
	            $this->shippingService->set(ShippingService::getInstanceByID($serviceId));
	        }
	        else
	        {
	            $this->shippingServiceData->set(serialize($rate));
	        }
	
	        // reset amounts...
	        $this->amount->set(0);
	        $this->shippingAmount->set(0);
	        $this->taxAmount->set(0);
	                
	        // ... and recalculated them
	        $this->recalculateAmounts();
        }
        
        $this->status->set(self::STATUS_NEW);
        
        $ret = parent::insert();
        
        // save ordered items
        foreach ($this->items as $item)
        {
            $item->shipment->set($this);
            $item->save();
        }
        
        // save taxes
        foreach ($this->getTaxes() as $tax)
        {
            $tax->save();
        }
                            
        return $ret;
    }
    
    public function getItems()
    {
	    $this->loadItems();
        return $this->items;
    }
    
    public function unloadItems()
    {
        $this->items = null;
    }    
    
    public function recalculateAmounts()
    {
        $this->loadItems();
        
        $currency = $this->order->get()->currency->get();
        $this->amountCurrency->set($currency);
        $this->amount->set($this->getSubTotal($currency, self::WITHOUT_TAXES));
        
        // total taxes
        $taxes = 0;
        foreach ($this->getTaxes() as $tax)
        {
            $tax->recalculateAmount(false);
            $taxes += $tax->getAmountByCurrency($currency);   
        }
        $this->taxAmount->set($taxes);
       
        // shipping rate
        if ($rate = $this->getSelectedRate())
        {
            $this->shippingAmount->set($rate->getAmountByCurrency($currency));            
        }
    }
    
    public function applyTaxesToAmount($amount)
    {
        foreach ($this->getTaxes() as $tax)
        {
            $amount = $tax->taxRate->get()->applyTax($amount);
        }        
        
        return $amount;
    }
}

?>