<?php

ClassLoader::import("application.model.tax.*");
ClassLoader::import("application.model.delivery.*");

/**
 * Defines a tax. Actual tax rates have to be defined for each DeliveryZone separately.
 *
 * @package application.model.tax
 * @author Integry Systems <http://integry.com>
 */
class Tax extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Tax");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(2)));
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
	 * Create new tax
	 *
	 * @param string $$defaultLanguageName Type name spelled in default language
	 * @return Tax
	 */
	public static function getNewInstance($defaultLanguageName)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	$instance->setValueByLang('name', null, $defaultLanguageName);

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
		$filter->setOrder(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
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
	public static function getTaxes(DeliveryZone $notUsedInThisZone = null, $loadReferencedRecords = false)
	{
		$filter = new ARSelectFilter();
		$rates = TaxRate::getRecordSetByDeliveryZone($notUsedInThisZone);

		if($rates->getTotalRecordCount() > 0)
		{
			$zoneRatesIDs = array();
			foreach($rates as $rate)
			{
				$taxIDs[] = $rate->tax->get()->getID();
			}

			$notInCond = new NotINCond(new ARFieldHandle(__CLASS__, "ID"), $taxIDs);
			$filter->setCondition($notInCond);
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
		$f = select();
		$f->setOrder(f('Tax.position'));
		return self::getRecordSet($f, $loadReferencedRecords);
	}

	public function includesTax(Tax $tax)
	{
		return $tax->position->get() < $this->position->get();
	}

	protected function insert()
	{
	  	$this->setLastPosition();

		parent::insert();
	}
}

?>