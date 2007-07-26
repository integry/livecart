<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.delivery.*");

/**
 * Define rules for shipping cost calculation, which can be based on shipment weight, subtotal,
 * number of items, etc. Each ShippingRate entity defines one concrete shipping cost calculation
 * formula for a defined shipping weight or subtotal interval. ShippingRate's belong to ShippingService
 * entities.
 *
 * Shipping rate is being calculated as follows:
 *
 * weight based rates:
 *     rate = flatCharge + (itemCount * perItemCharge) + (shipmentWeight * perKgCharge)
 *
 * subtotal based rates:
 *     rate = flatCharge + (itemCount * perItemCharge) + (shipmentSubtotal * subtotalPercentCharge)
 *
 * @package application.model.delivery
 * @author Integry Systems <http://integry.com> 
 */
class ShippingRate extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ShippingRate");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingServiceID", "ShippingService", "ID", "ShippingService", ARInteger::instance()));
		
		$schema->registerField(new ARField("weightRangeStart", ARFloat::instance()));
		
		$schema->registerField(new ARField("weightRangeEnd", ARFloat::instance()));
		$schema->registerField(new ARField("subtotalRangeStart", ARFloat::instance()));
		$schema->registerField(new ARField("subtotalRangeEnd", ARFloat::instance()));
		$schema->registerField(new ARField("flatCharge", ARFloat::instance()));
		$schema->registerField(new ARField("perItemCharge", ARFloat::instance()));
		$schema->registerField(new ARField("subtotalPercentCharge", ARFloat::instance()));
		$schema->registerField(new ARField("perKgCharge", ARFloat::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ShippingRate
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new shipping rate instance
	 * 
	 * @param ShippingService $shippingService Shipping service instance
	 * @param float $rangeStart Lower range limit
	 * @param float $rangeEnd Higher range limit
	 * @return ShippingRate
	 */
	public static function getNewInstance(ShippingService $shippingService, $rangeStart, $rangeEnd)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	$instance->shippingService->set($shippingService);
	  	
	  	$instance->setRangeStart($rangeStart);
	  	$instance->setRangeEnd($rangeEnd);
	  	
	  	return $instance;
	}
	
	public function setRangeStart($rangeStart)
	{
	    return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeStart->set($rangeStart) : $this->subtotalRangeStart->set($rangeStart);
	}
	
	public function setRangeEnd($rangeEnd)
	{
	    return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeEnd->set($rangeEnd) : $this->subtotalRangeEnd->set($rangeEnd);
	}
	
	public function getRangeStart()
	{
	    return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeStart->get() : $this->subtotalRangeStart->get();
	}
	
	public function getRangeEnd()
	{
	    return ($this->getRangeType() == ShippingService::WEIGHT_BASED) ? $this->weightRangeEnd->get() : $this->subtotalRangeEnd->get();
	}
	
	public function getRangeType()
	{
	    if(!$this->isLoaded() && $this->isExistingRecord())
	    {
	        $this->load();
	    }
	    
	    $service = $this->shippingService->get();
	    if(!$service->isLoaded()) 
	    {
	        $service->load();
	    }
	    
	    return $service->rangeType->get();
	}

	/**
	 * Load service rates record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}
	
	/**
	 * Load service rates from known service
	 *
	 * @param ShippingService $service
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSetByService(ShippingService $service, $loadReferencedRecords = false)
	{
 	    $filter = new ARSelectFilter();

		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "shippingServiceID"), $service->getID()));
		
		return self::getRecordSet($filter, $loadReferencedRecords);
	}

    public function toArray($forse = false)
    {
        $array = parent::toArray($forse);
        return $array;
//        $prices = $this->getPricingHandler()->toArray();
//        foreach($prices['calculated'] as $code => $value)
//        {
//            $prices["price_$code"] = $value;
//        }
//
//        return $prices;
    }
}

?>