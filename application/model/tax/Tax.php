<?php

ClassLoader::import("application.model.delivery.*");

/**
 * Taxes
 *
 * @package application.model.delivery
 */
class Tax extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Tax");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return Tax
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new tax rate
	 * 
	 * @param string $$defaultLanguageName Type name spelled in default language
	 * @return Tax
	 */
	public static function getNewInstance($defaultLanguageName)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	$instance->setValueByLang('name', Store::getInstance()->getDefaultLanguageCode(), $defaultLanguageName);
        
	  	return $instance;
	}


	/**
	 * Load taxes record set
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
	 * Get a list of existing taxes
	 * 
	 * @param boolean $includeDisabled Include disabled taxes in this list
	 * @param TaxRate $doNotBelongToRate Don not belong to specified rate
	 * @param boolean $loadReferencedRecords Load referenced records
	 * 
	 * @return ARSet
	 */
	public static function getTaxes($includeDisabled = false, DeliveryZone $notUsedInThisZone = null, $loadReferencedRecords = false)
	{
	    $filter = new ARSelectFilter();
	    
	    if(!$includeDisabled)
	    {
   		    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "isEnabled"), 1));
	    }
	    
        $rates = TaxRate::getRecordSetByDeliveryZone($notUsedInThisZone, true);

        if($rates->getTotalRecordCount() > 0)
        {
            $zoneRatesIDs = array();
            foreach($rates as $rate)
            {
                $taxIDs[] = $rate->tax->get()->getID();
            }
            
            $filter->setCondition(new NotINCond(new ARFieldHandle(__CLASS__, "ID"), $taxIDs));
        }	
	    
	    return self::getRecordSet($filter, $loadReferencedRecords);
	}
	
	
	/**
	 * Get a list of all existing taxes
	 * 
	 * @param boolean $loadReferencedRecords Load referenced records
	 * 
	 * @return ARSet
	 */
	public static function getAllTaxes($loadReferencedRecords = false)
	{
	    return self::getRecordSet(new ARSelectFilter(), $loadReferencedRecords);
	}
}

?>