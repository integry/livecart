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

	// 
	public static function getAssignedToArray( Product $product)
	{
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'productID'), $product->getID()));
		return self::getRecordSetArray($f);
	}
	
	public static function getInstanceByID($recordID, $loadRecordData = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData);
	}
	
	public function toArray()
	{
		$array = parent::toArray();
		$rs = ProductPrice::getRecurringProductPeriodPrices($this);
		if ($rs && $rs->size())
		{
			while(false != ($item = $rs->shift()))
			{
				$itemArray = $item->toArray();
				switch($item->type->get())
				{
					case ProductPrice::TYPE_SETUP_PRICE:
						$array['ProductPrice_setup'][$itemArray['currencyID']] = $itemArray;
						break;
					case ProductPrice::TYPE_PERIOD_PRICE:
						$array['ProductPrice_period'][$itemArray['currencyID']] = $itemArray;
						break;
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

}

?>