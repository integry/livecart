<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.category.CategoryImage");
ClassLoader::import("application.model.delivery.*");

/**
 * Hierarchial product category model class
 *
 * @package application.model.delivery
 */
class DeliveryZone extends MultilingualObject 
{
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
    	// determine if the country has states defined in database
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle('State', 'countryID'), $address->countryID->get()));
		$hasStates = (ActiveRecordModel::getRecordCount($f) > 0);    
		
		// get zones that match country
		
		// leave zones that match state
		
		// leave zones that match filters
		
		return DeliveryZone::getInstanceByID(0);		
    }
    
    /**
     *
     *	@return ShippingRateSet
     */
	public function getDefinedShippingRates(CustomerOrder $order)
    {
		// stub
		ClassLoader::import('library.shipping.ShippingRateSet');
		ClassLoader::import('library.shipping.ShippingRateResult');
		
		$rates = new ShippingRateSet();
		
		$rate1 = new ShippingRateResult();
		$rate1->setServiceName('Test rate 1');
		$rate1->setCost(10, 'USD');
		$rate1->setServiceID(12);

		$rate2 = new ShippingRateResult();
		$rate2->setServiceName('Test rate 2');
		$rate2->setCost(23.45, 'USD');
		$rate1->setServiceID(13);
				
		$rates->add($rate1);
		$rates->add($rate2);
				
		return $rates;		
	}
	
    /**
     *
     *	@return ARSet
     */
	public function getTaxRates()
	{
		
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
}

?>