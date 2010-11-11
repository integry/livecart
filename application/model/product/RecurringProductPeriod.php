<?php

ClassLoader::import('application.model.ActiveRecordModel');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class RecurringProductPeriod extends MultilingualObject
{
	const TYPE_PERIOD_DAY = 0;
	const TYPE_PERIOD_WEEK = 1;
	const TYPE_PERIOD_MONTH = 2;
	const TYPE_PERIOD_YEAR = 3;

	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName(__CLASS__);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("periodType", ARInteger::instance()));
		$schema->registerField(new ARField("periodLength", ARInteger::instance()));
		$schema->registerField(new ARField("rebillCount", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
	}

	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		$filter->setOrder(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		$filter->setOrder(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
		return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}

	public static function getRecordSetArrayByProduct($productOrID)
	{
		return self::getRecordSetArray(self::getAsignedToFilter($productOrID));
	}

	public static function getRecordSetByProduct($productOrID)
	{
		return self::getRecordSet(self::getAsignedToFilter($productOrID));
	}

	private static function getAsignedToFilter($productOrID)
	{
		if (!is_numeric($productOrID) && $productOrID instanceof Product == false)
		{
			throw new Exception('getAsignedToFilter() excpects argument to be instance of Product');
		}
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'productID'), is_numeric($productOrID) ? $productOrID : $productOrID->getID()));
		return $f;
	}

	public static function getInstanceByID($recordID, $loadRecordData = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData);
	}

	public function toArray($currencyID=null)
	{
		$array = parent::toArray();
		$rs = ProductPrice::getRecurringProductPeriodPrices($this, $currencyID);
		$currencies = array();
		if ($rs && $rs->size())
		{
			$mapping = array(
				ProductPrice::TYPE_PERIOD_PRICE => 'ProductPrice_period',
				ProductPrice::TYPE_SETUP_PRICE => 'ProductPrice_setup'
			);

			while(false != ($item = $rs->shift()))
			{
				$itemArray = $item->toArray();
				$type = $item->type->get();
				if (array_key_exists($type, $mapping))
				{
					$array[$mapping[$type]][$itemArray['currencyID']] = $itemArray;
					if (array_key_exists($itemArray['currencyID'], $currencies) == false)
					{
						$currencies[$itemArray['currencyID']] = Currency::getInstanceByID($itemArray['currencyID']);
					}
					$array[$mapping[$type]]['formated_price'][$itemArray['currencyID']] = $currencies[$itemArray['currencyID']]->getFormattedPrice($itemArray['price']);
				}
			}
		}
		return $array;
	}

	public static function getNewInstance(Product $product)
	{
		$instance = ActiveRecord::getNewInstance(__CLASS__);
		//$instance->setValueByLang('name', null, $defaultLanguageName);
		$instance->productID->set($product);
		return $instance;
	}

	const PERIOD_TYPE_NAME_SINGLE = 0;
	const PERIOD_TYPE_NAME_PLURAL = 1;

	public static function getAllPeriodTypes($type)
	{
		if ($type == RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL)
		{
			return array(
				RecurringProductPeriod::TYPE_PERIOD_DAY => '_type_period_days',
				RecurringProductPeriod::TYPE_PERIOD_WEEK => '_type_period_weeks',
				RecurringProductPeriod::TYPE_PERIOD_MONTH => '_type_period_months',
				RecurringProductPeriod::TYPE_PERIOD_YEAR => '_type_period_years'
			);
		}
		else if($type == RecurringProductPeriod::PERIOD_TYPE_NAME_SINGLE)
		{
			return array(
				RecurringProductPeriod::TYPE_PERIOD_DAY => '_type_period_day',
				RecurringProductPeriod::TYPE_PERIOD_WEEK => '_type_period_week',
				RecurringProductPeriod::TYPE_PERIOD_MONTH => '_type_period_month',
				RecurringProductPeriod::TYPE_PERIOD_YEAR => '_type_period_year'
			);
		}
		else
		{
			// throw ..
		}
	}
}

?>