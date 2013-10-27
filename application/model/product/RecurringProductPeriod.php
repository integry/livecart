<?php


/**
 *
 * @package application/model/product
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

		public $ID;
		public $productID", "Product", "ID", null, ARInteger::instance()));
		public $position;
		public $periodType;
		public $periodLength;
		public $rebillCount;
		public $name;
		public $description;
	}

	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		$filter->orderBy(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	public static function getRecordSetArray(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		$filter->orderBy(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
		return parent::getRecordSetArray(__CLASS__, $filter, $loadReferencedRecords);
	}

	public static function getRecordSetArrayByProduct($productOrID, $filter = null)
	{
		return self::getRecordSetArray(self::getAsignedToFilter($productOrID, $filter));
	}

	public static function getRecordSetByProduct($productOrID, $filter = null)
	{
		return self::getRecordSet(self::getAsignedToFilter($productOrID, $filter));
	}

	private static function getAsignedToFilter($productOrID, $filter=null)
	{
		if (!$filter)
		{
			$filter = new ARSelectFilter();
		}

		if (is_numeric($productOrID))
		{
			$productIDs = array((int)$productOrID);
		}
		else if(is_array($productOrID))
		{
			$productIDs = $productOrID;
		}
		else if(is_object($productOrID) && $productOrID instanceof Product) // is_callable(array($productOrID, 'getID'))
		{
			$productIDs = array($productOrID->getID());
		}
		else
		{
			throw new Exception('getAsignedToFilter() excpects argument to be instance of Product');
		}
		$filter->andWhere(new InCond(new ARFieldHandle(__CLASS__, 'productID'), $productIDs));
		return $filter;
	}

	public static function getInstanceByID($recordID, $loadRecordData = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData);
	}

	public static function getRecordSetArrayByIDs($recordIDs, $loadRecordData = false)
	{
		if (!is_array($recordIDs))
		{
			$recordIDs = array($recordIDs);
		}
		$filter = new ARSelectFilter();
		$filter->setCondition(new InCond(new ARFieldHandle(__CLASS__, 'ID'), $recordIDs));
		// ActiveRecordModel::getRecordSetArray() will not get required setup and period prices!
		$rs = ActiveRecordModel::getRecordSet(__CLASS__, $filter);
		$result = array();
		foreach($rs->toArray() as $item)
		{
			$result[$item['ID']] = $item;
		}
		return $result;
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
				if ($itemArray['type'] == ProductPrice::TYPE_SETUP_PRICE || $itemArray['type'] == ProductPrice::TYPE_PERIOD_PRICE)
				{
					$array[$mapping[$itemArray['type']]][$itemArray['currencyID']] = $itemArray;
					if (array_key_exists($itemArray['currencyID'], $currencies) == false)
					{
						$currencies[$itemArray['currencyID']] = Currency::getInstanceByID($itemArray['currencyID']);
					}
					$array[$mapping[$itemArray['type']]]['formated_price'][$itemArray['currencyID']] = $currencies[$itemArray['currencyID']]->getFormattedPrice($itemArray['price']);
				}
			}
		}
		return $array;
	}

	public static function getNewInstance(Product $product)
	{
		$instance = new self();
		//$instance->setValueByLang('name', null, $defaultLanguageName);
		$instance->productID = $product;
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