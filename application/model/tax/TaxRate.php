<?php

ClassLoader::import("application.model.delivery.DeliveryZone");
ClassLoader::import("application.model.tax.Tax");
ClassLoader::import("application.model.tax.TaxClass");

/**
 * Defines a tax rate for a DeliveryZone. Tax rates are applied to order totals and shipping charges as well.
 *
 * @package application.model.tax
 * @author Integry Systems <http://integry.com>
 */
class TaxRate extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("TaxRate");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("taxID", "Tax", "ID", "Tax", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("taxClassID", "TaxClass", "ID", "TaxClass", ARInteger::instance()));

		$schema->registerField(new ARField("rate", ARFloat::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return TaxRate
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}

	/**
	 * Create new tax rate
	 *
	 * @param DeliveryZone $deliveryZone Delivery zone instance
	 * @param Tax $tax Tax type
	 * @param float $rate Rate in percents
	 * @return TaxRate
	 */
	public static function getNewInstance(DeliveryZone $deliveryZone = null, Tax $tax, $rate)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);

		if($deliveryZone)
		{
			$instance->deliveryZone->set($deliveryZone);
		}

	  	$instance->tax->set($tax);
	  	$instance->rate->set($rate);

	  	return $instance;
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
		if (!$loadReferencedRecords)
		{
			$loadReferencedRecords = array('Tax');
		}
		$filter->setOrder(new ARFieldHandle('Tax', 'position'));

		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/**
	 * Load rates from known delivery zone
	 *
	 * @param DeliveryZone $deliveryZone
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSetByDeliveryZone(DeliveryZone $deliveryZone = null, $loadReferencedRecords = array('Tax'))
	{
 		$filter = new ARSelectFilter();

		if(!$deliveryZone || $deliveryZone->isDefault())
		{
			$filter->setCondition(new IsNullCond(new ARFieldHandle(__CLASS__, "deliveryZoneID")));
		}
		else
		{
			$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "deliveryZoneID"), $deliveryZone->getID()));
		}

		return self::getRecordSet($filter, $loadReferencedRecords);
	}

	public function applyTax($amount)
	{
		return $amount + $this->getTaxAmount($amount);
	}

	public function getTaxAmount($amount)
	{
		return $amount * ($this->rate->get() / 100);
	}

	protected function insert()
	{
		if (!$this->deliveryZone->get() || $this->deliveryZone->get()->isDefault())
		{
			$this->deliveryZone->setNull();
		}

		return parent::insert();
	}
}

?>