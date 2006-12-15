<?php

/**
 *
 * @package application.model
 * @author Denis Slaveckij <denis@integry.net>
 *
 */
class Currency extends ActiveRecord
{

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Currency");

		$schema->registerField(new ARPrimaryKeyField("ID", ArChar::instance(3)));

		$schema->registerField(new ARField("rate", ArFloat::instance(16)));
		$schema->registerField(new ARField("lastUpdated", ArDateTime::instance()));
		$schema->registerField(new ARField("isDefault", ArBool::instance()));
		$schema->registerField(new ARField("isEnabled", ArBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}

	public function setAsDefault($default = true)
	{
	  	$this->isDefault->set((bool)$default);
	}

	public static function getCurrencies()
	{
		return ActiveRecord::getRecordSet("Currency", new ArSelectFilter(), true);
	}

	/**
	 * Gets default Currency.
	 * @return ActiveRecord
	 */
	public static function getDefaultCurrency()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle("Currency", "isDefault"), 1));

		$set = Currency::getRecordSet("Currency", $filter, true);

		if (count($set->getIterator()) == 0)
		{
			return false;
		}

		return $set->getIterator()->current();
	}

	/**
	 * Gets default currency from ArSet.
	 * @param ArSet $currSet
	 * @return ActiveRecord
	 */
	public static function getDefaultCurrencyFromSet($currSet)
	{
		foreach($currSet as $value)
		{
			if ($value->isDefault->get() == 1)
			{
				return $value;
			}
		}
	}
}

?>
