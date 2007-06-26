<?php

ClassLoader::import("application.model.delivery.*");
ClassLoader::import('library.shipping.ShippingRateSet');

/**
 * Delivery zones are used to classify shipping locations, which allows to define different
 * shipping rates and taxes for different delivery addresses.
 *
 * Delivery zone is determined automatically when user proceeds with the checkout and enters
 * the shipping address. In case no rules match for the shipping zone, the default delivery
 * zone is used.
 *
 * The delivery zone address rules can be set up in several ways - by assigning whole countries
 * or states or by defining mask strings, that allow to recognize addresses by city names, postal
 * codes or even street addresses.
 *
 * @package application.model.delivery
 * @author Integry Systems <http://integry.com>
 */
class DeliveryZone extends MultilingualObject 
{
    const ENABLED_TAXES = false;
    
    public function __construct($data = array())
    {
        parent::__construct($data);
    }
    
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("DeliveryZone");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("isEnabled", ARInteger::instance(1)));
		$schema->registerField(new ARField("isFreeShipping", ARInteger::instance(1)));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * 
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return DeliveryZone
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * @return DeliveryZone
	 */
	public static function getNewInstance()
	{
	  	return ActiveRecord::getNewInstance(__CLASS__);
	}
	
	/**
	 * @return ARSet
	 */
	public static function getAll()
	{
	    $filter = new ARSelectFilter();
	    return self::getRecordSet(__CLASS__, $filter);
	}

	/**
	 * @return ARSet
	 */
	public static function getEnabled()
	{
	    $filter = new ARSelectFilter();
	    $filter->setCondition(new EqualsCond(new ArFieldHandle(__CLASS__, "isEnabled"), 1));
	    	    
	    return self::getRecordSet(__CLASS__, $filter);
	}

	/**
	 * Returns the delivery zone, which matches the required address
	 *
     * @return DeliveryZone
	 * @todo implement
	 */
    public static function getZoneByAddress(UserAddress $address)
    {
		if (!$address->isLoaded())
		{
            $address->load();    
        }
        
        $zones = array();
		    
    	// get zones by state
    	if ($address->state->get())
    	{
            $f = new ARSelectFilter();
		    $f->setCondition(new EqualsCond(new ARFieldHandle('DeliveryZoneState', 'stateID'), $address->state->get()->getID()));
		    $s = ActiveRecordModel::getRecordSet('DeliveryZoneState', $f, ActiveRecordModel::LOAD_REFERENCES);
		    
            foreach ($s as $zone)
            {
                $zones[] = $s->deliveryZone->get();
            }    
        }
        
		// get zones by country
		if (!$zones)
		{
            $f = new ARSelectFilter();
		    $f->setCondition(new EqualsCond(new ARFieldHandle('DeliveryZoneCountry', 'countryCode'), $address->countryID->get()));
		    $s = ActiveRecordModel::getRecordSet('DeliveryZoneCountry', $f, ActiveRecordModel::LOAD_REFERENCES);

            foreach ($s as $zone)
            {
                $zones[] = $zone->deliveryZone->get();
            }                
        }
		
		$maskPoints = array();
		
		// leave zones that match masks
		foreach ($zones as $key => $zone)
		{
            $match = $zone->getMaskMatch($address);
            if (!$match)
            {
                unset($zones[$key]);       
            }
            else
            {
                $maskPoints[$key] = $match;
            }
        }
		
		if ($maskPoints)
		{
            asort($maskPoints);
            end($maskPoints);
            return $zones[key($maskPoints)];
        }
		
		return $zones ? array_shift($zones) : DeliveryZone::getDefaultZoneInstance();
    }
    
	/**
	 * Returns the default delivery zone instance
	 *
     * @return DeliveryZone
	 */
    public static function getDefaultZoneInstance()
    {
        return self::getInstanceById(0);    
    }    
    
	/**
	 * Determine if the supplied UserAddress matches address masks
	 *
     * @return bool
	 */
    public function getMaskMatch(UserAddress $address)
    {
        return 
               $this->hasMaskGroupMatch($this->getCityMasks(), $address->city->get()) +
               
               ($this->hasMaskGroupMatch($this->getAddressMasks(), $address->address1->get()) ||                
               $this->hasMaskGroupMatch($this->getAddressMasks(), $address->address2->get())) +
                         
               $this->hasMaskGroupMatch($this->getZipMasks(), $address->postalCode->get());                   
    }
    
    private function hasMaskGroupMatch(ARSet $masks, $addressString)
    {
        if (!$masks->size())
        {
            return true;
        }
        
        $match = false;
        
        foreach ($masks as $mask)
        {
            if ($this->isMaskMatch($addressString, $mask->mask->get()))
            {
                $match = 2;
            }
        }
        
        return $match;
    }
    
    private function isMaskMatch($addressString, $maskString)
    {
        $maskString = str_replace('*', '.*', $maskString);
        $maskString = str_replace('?', '.{0,1}', $maskString);
        return preg_match('/' . $maskString . '/im', $addressString);
    }
    
    /**
     *  Returns manually defined shipping rates for the particular shipment
     *
     *	@return ShippingRateSet
     */
	public function getDefinedShippingRates(Shipment $shipment)
    {
		$rates = new ShippingRateSet();
		
		foreach ($this->getShippingServices() as $service)
		{
            $rate = $service->getDeliveryRate($shipment);
            if ($rate)
            {
                $rates->add($rate);
            }
        }
				
		return $rates;		
	}
	
    /**
     *  Returns real time shipping rates for the particular shipment
     *
     *	@return ShippingRateSet
     */
	public function getRealTimeRates(Shipment $shipment)
	{
		$rates = new ShippingRateSet();
        
        $store = Store::getInstance();
        $handlers = $store->getEnabledRealTimeShippingServices();
        foreach ($handlers as $handler)
        {            
            $rates->merge(ShipmentDeliveryRate::getRealTimeRates($store->getShippingHandler($handler), $shipment));
        }
                
//        foreach ($rates as $rate) { var_dump($rate); }        
        
		return $rates;
    }
    
    /**
     *  Returns both real time and calculated shipping rates for the particular shipment
     *
     *	@return ShippingRateSet
     */
    public function getShippingRates(Shipment $shipment)
    {
        $defined = $this->getDefinedShippingRates($shipment);
        $defined->merge($this->getRealTimeRates($shipment));   
        
        // apply taxes
        foreach ($defined as $rate)
        {
            $rate->setAmountWithTax($shipment->applyTaxesToAmount($rate->getCostAmount()));
        }
         
        return $defined;
    }
	
    /**
     *
     *	@return ARSet
     */
	public function getTaxRates($includeDisabled = true, $loadReferencedRecords = array('Tax'))
	{
		return TaxRate::getRecordSetByDeliveryZone($this, $includeDisabled, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getCountries($loadReferencedRecords = false)
	{
	    return DeliveryZoneCountry::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getStates($loadReferencedRecords = false)
	{
	    return DeliveryZoneState::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getCityMasks($loadReferencedRecords = false)
	{
	    return DeliveryZoneCityMask::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getZipMasks($loadReferencedRecords = false)
	{
	    return DeliveryZoneZipMask::getRecordSetByZone($this, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public function getAddressMasks($loadReferencedRecords = false)
	{
	    return DeliveryZoneAddressMask::getRecordSetByZone($this, $loadReferencedRecords);
	}

	
	/**
	 * Get set of shipping sevices available in current zone
	 * 
	 * @param boolean $loadReferencedRecords
	 * @return ARSet
	 */
	public function getShippingServices($loadReferencedRecords = false)
	{
	    return ShippingService::getByDeliveryZone($this, $loadReferencedRecords);
	}
}

?>