<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.delivery.*");

/**
 * Pre-defined shipping service plan, that is assigned to a particular DeliveryZone. 
 * Each ShippingService entity can contain several ShippingRate entities to determine
 * the actual shipping rates.
 *
 * In addition to pre-defined rates, it is also possible to use real-time shipping
 * rate calculation with integrated postal company web services.
 *
 * @package application.model.delivery
 * @author Integry Systems <http://integry.com> 
 */
class ShippingService extends MultilingualObject 
{
    const WEIGHT_BASED = 0;
    const SUBTOTAL_BASED = 1;
    
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ShippingService");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(10)));
		$schema->registerField(new ARField("rangeType", ARInteger::instance(1)));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Gets an existing record instance
	 * 
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return ShippingService
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new shipping service
	 * 
	 * @param DeliveryZone $deliveryZone Delivery zone
	 * @param string $defaultLanguageName Service name in default language
	 * @param integer $calculationCriteria Shipping price calculation criteria. 0 for weight based calculations, 1 for subtotal based calculations
	 * @return ShippingService
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone = null, $defaultLanguageName, $calculationCriteria)
	{
        $instance = parent::getNewInstance(__CLASS__);
        if($deliveryZone)
        {
            $instance->deliveryZone->set($deliveryZone);
        }
        $instance->setValueByLang('name', null, $defaultLanguageName);
        $instance->rangeType->set($calculationCriteria);
        
        return $instance;
	}

	/**
	 * Load delivery services record set
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
	
	/*####################  Instance retrieval ####################*/		
	
	/**
	 * Load delivery services record by Delivery zone
	 *
	 * @param DeliveryZone $deliveryZone
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getByDeliveryZone(DeliveryZone $deliveryZone = null, $loadReferencedRecords = false)
	{
 	    $filter = new ARSelectFilter();

		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"), 'ASC');
		
		if (!$deliveryZone)
		{
            $deliveryZone = DeliveryZone::getDefaultZoneInstance();
        }
        
        if ($deliveryZone->isDefault())
		{
		    $filter->setCondition(new IsNullCond(new ARFieldHandle(__CLASS__, "deliveryZoneID")));
		}
		else
		{
		    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "deliveryZoneID"), $deliveryZone->getID()));
		}
		
        $services = self::getRecordSet($filter, $loadReferencedRecords);
		
        if ($deliveryZone->isDefault())
        {
            foreach ($services as $service)
            {
                $service->deliveryZone->set($deliveryZone);
            }
        }
		
		return $services;		
	}
	
	/*####################  Get related objects ####################*/ 	
	
	/**
	 * Get active record set from current service
	 * 
	 * @param boolean $loadReferencedRecords
	 * @return ARSet
	 */
	public function getRates($loadReferencedRecords = false)
	{
	    return ShippingRate::getRecordSetByService($this, $loadReferencedRecords);
	}
	
	/**
	 * Calculate a delivery rate for a particular shipment
	 *
	 * @return ShipmentDeliveryRate
	 */
    public function getDeliveryRate(Shipment $shipment)
    {
        // get applicable rates
        if (self::WEIGHT_BASED == $this->rangeType->get())
        {
            $weight = $shipment->getChargeableWeight($this->deliveryZone->get());
            $cond = new EqualsOrLessCond(new ARFieldHandle('ShippingRate', 'weightRangeStart'), $weight);
            $cond->addAND(new EqualsOrMoreCond(new ARFieldHandle('ShippingRate', 'weightRangeEnd'), $weight));
        }    
        else
        {
            $total = $shipment->getSubTotal(self::getApplication()->getDefaultCurrency(), Shipment::WITHOUT_TAXES);
            $cond = new EqualsOrLessCond(new ARFieldHandle('ShippingRate', 'subtotalRangeStart'), $total);
            $cond->addAND(new EqualsOrMoreCond(new ARFieldHandle('ShippingRate', 'subtotalRangeEnd'), $total));
        }

        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ShippingRate', 'shippingServiceID'), $this->getID()));
        $f->mergeCondition($cond);

        $rates = ActiveRecordModel::getRecordSet('ShippingRate', $f);
        
        if (!$rates->size())
        {
            return null;
        }        
        
        $itemCount = $shipment->getChargeableItemCount($this->deliveryZone->get()); 
        
        if (!$itemCount && $this->deliveryZone->get()->isFreeShipping->get())
        {
            // free shipping
            $maxRate = 0;
        }
        else
        {
            $maxRate = 0;
            
            foreach ($rates as $rate)
            {
                $charge = $rate->flatCharge->get() + ($itemCount * $rate->perItemCharge->get());

                if (self::WEIGHT_BASED == $this->rangeType->get())
                {
                    $charge += ($rate->perKgCharge->get() * $weight);
                }    
                else
                {
                    $charge += ($rate->subtotalPercentCharge->get() / 100) * $total;
                }                            

                if ($charge > $maxRate)
                {
                    $maxRate = $charge;
                }
            }
        }
        
        return ShipmentDeliveryRate::getNewInstance($this, $maxRate);
    }	
    
    public function save($forceOperation = null)
    {
		if ($this->deliveryZone->get() && (0 == $this->deliveryZone->get()->getID()))
		{
			$this->deliveryZone->set(null);
		}
		
		return parent::save($forceOperation);
	}
}
?>