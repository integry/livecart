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

		$schema->registerField(new ARPrimaryKeyField("ID", Char::instance(3)));

		$schema->registerField(new ARField("rate", Float::instance(16)));
		$schema->registerField(new ARField("lastUpdated", DateTime::instance()));
		$schema->registerField(new ARField("isDefault", Bool::instance()));
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

	/**
	 * Sets default currency.
	 * @param string $ID currency id
	 */
	public static function setDefault($ID)
	{
		$currSet = ActiveRecord::getRecordSet("Currency", new ArSelectFilter(), true);

		$default = Currency::getDefaultCurrencyFromSet($currSet);
		$new = ActiveRecord::getInstanceById("Currency", $ID, true);

		$rate = $new->rate->get();

		foreach($currSet as $record)
		{
			if ($record->getId() == $ID)
			{
				$record->rate->setNull();
				$record->isDefault->set(1);
			}
			else if ($record->getId() == $default->getId())
			{
				$record->rate->set(1 / $rate);
				$record->isDefault->set(0);
			}
			else
			{
				$record->rate->set($record->rate->get() / $rate);
			}
			$record->save();
		}
	}
}

?>
