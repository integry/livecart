<?php


/**
 *
 * @author Integry Systems
 * @package application/model/discount
 */
class DiscountCondition extends ActiveTreeNode implements MultilingualObjectInterface
{
	// equal
	const COMPARE_EQ = 0;

	// less than or equal
	const COMPARE_LTEQ = 1;

	// greater than or equal
	const COMPARE_GTEQ = 2;

	// not equal
	const COMPARE_NE = 3;

	// divisable
	const COMPARE_DIV = 4;

	// non-divisable
	const COMPARE_NDIV = 5;

	const COUPON_LIMIT_ALL = 0;
	const COUPON_LIMIT_USER = 1;

	// custom types
	const TYPE_PAYMENT_METHOD = 101;

	private $records = array();

	private $subConditions = null;

	/**
	 * Define database schema for Category model
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{

		parent::defineSchema($className);

		public $isEnabled;
		public $isAnyRecord;
		public $isAllSubconditions;
		public $isActionCondition;
		public $isFinal;
		public $isReverse;

		public $conditionClass;

		public $recordCount;
		public $validFrom;
		public $validTo;
		public $subTotal;
		public $count;
		public $comparisonType;
		public $position;
		public $name;
		public $description;
		public $couponCode;
		public $couponLimitCount;
		public $couponLimitType;
		public $serializedCondition;
	}

	public function loadAll()
	{
		$set = new ARSet();
		$set->add($this);
		$subConditions = self::loadSubConditions($set);
		$subConditions->add($this);
		self::loadConditionRecords($subConditions, 'DiscountConditionRecord');
		//self::loadConditionRecords($subConditions, array('Category' => array('DiscountConditionRecord'), 'Product', 'Manufacturer', 'User', 'UserGroup', 'DeliveryZone'));
	}

	public function registerRecord(DiscountConditionRecord $record)
	{
		$this->records[$record->getID()] = $record;
	}

	public function getRecords()
	{
		return $this->records;
	}

	public function registerSubCondition(DiscountCondition $condition)
	{
		$this->subConditions[$condition->getID()] = $condition;
	}

	private function hasSubConditions()
	{
		return ($this->rgt->get() - $this->lft->get()) > 1;
	}

	public static function getRootNode()
	{
		if (!$instance = self::getInstanceByIDIfExists(__CLASS__, self::ROOT_ID, false))
		{
			$instance = ActiveRecordModel::getNewInstance(__CLASS__);
			$instance->setID(self::ROOT_ID);
			$instance->isEnabled = true);
			$instance->lft = 1);
			$instance->rgt = 2);
			$instance->save();
		}

		return $instance;
	}

	public static function isCouponCodes()
	{
		$c = new NotEqualsCond(new ARFieldHandle(__CLASS__, 'couponCode'), '');
		$c->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'isEnabled'), 1));
		return ActiveRecordModel::getRecordCount(__CLASS__, new ARSelectFilter($c));
	}

	public static function getInstanceByCoupon($code)
	{
		$c = new EqualsCond(new ARFieldHandle(__CLASS__, 'couponCode'), $code);
		$c->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'isEnabled'), 1));
		$set = ActiveRecordModel::getRecordSet(__CLASS__, new ARSelectFilter($c));
		if ($set->size())
		{
			return $set->get(0);
		}
	}

	public static function getNewInstance(DiscountCondition $parentCondition = null)
	{
		if (!$parentCondition)
		{
			$parentCondition = self::getRootNode();
		}

		return parent::getNewInstance(__CLASS__, $parentCondition);
	}

	public static function loadConditionRecords(ARSet $conditionSet, $referencedRecords = array('Category'))
	{
		$ids = array();
		foreach ($conditionSet as $condition)
		{
			if ($condition->recordCount->get())
			{
				$ids[] = $condition->getID();
			}
		}

		if (!$ids)
		{
			return null;
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('DiscountConditionRecord', 'conditionID'), $ids));
		$f->setOrder(new ARFieldHandle('DiscountConditionRecord', 'categoryID'), 'DESC');
		$f->setOrder(new ARFieldHandle('DiscountConditionRecord', 'manufacturerID'), 'DESC');
		foreach (ActiveRecordModel::getRecordSet('DiscountConditionRecord', $f, $referencedRecords) as $record)
		{
			$record->condition->get()->registerRecord($record);
		}
	}

	public static function loadSubConditions(ARSet $conditionSet)
	{
		$cond = array();
		foreach ($conditionSet as $condition)
		{
			if ($condition->hasSubConditions())
			{
				$cond[] = $condition->getChildNodeCondition();
			}
		}

		if (!$cond)
		{
			return new ARSet();
		}

		$f = new ARSelectFilter(Condition::mergeFromArray($cond, true));
		$subConditions = ActiveRecordModel::getRecordSet(__CLASS__, $f);

		foreach ($subConditions as $condition)
		{
			$condition->parentNode->get()->registerSubCondition($condition);
		}

		return $subConditions;
	}

	public function getSubConditions()
	{
		if (is_null($this->subConditions))
		{
			$this->subConditions = array();
			self::loadSubConditions($this->initSet());
		}

		return $this->subConditions;
	}

	public function setValueByLang($fieldName, $langCode, $value)
	{
		return MultiLingualObject::setValueByLang($fieldName, $langCode, $value);
	}

	public function getValueByLang($fieldName, $langCode = null, $returnDefaultIfEmpty = true)
	{
		return MultiLingualObject::getValueByLang($fieldName, $langCode, $returnDefaultIfEmpty);
	}

	public function setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, Request $request)
	{
		return MultiLingualObject::setValueArrayByLang($fieldNameArray, $defaultLangCode, $langCodeArray, $request);
	}

	private function getSerializedCond()
	{
		return unserialize($this->getFieldValue('serializedCondition'));
	}

	private function setSerializedCond($cond)
	{
		return $this->setFieldValue('serializedCondition', serialize($cond));
	}

	public function getType()
	{
		$ser = $this->getSerializedCond();
		if (isset($ser['type']))
		{
			return $ser['type'];
		}
	}

	public function setType($type)
	{
		$ser = $this->getSerializedCond();

		if (isset($ser['type']) && ($ser['type'] != $type))
		{
			unset($ser['values']);
		}

		$ser['type'] = $type;
		$this->setSerializedCond($ser);
	}

	public function addValue($value)
	{
		$ser = $this->getSerializedCond();
		$ser['values'][$value] = true;
		$this->setSerializedCond($ser);
	}

	public function removeValue($value)
	{
		$ser = $this->getSerializedCond();
		unset($ser['values'][$value]);
		$this->setSerializedCond($ser);
	}

	public function setSerializedValue($type, $key, $value = null)
	{
		$ser = $this->getSerializedCond();

		if (is_null($key))
		{
			$ser[$type] = $value;
		}
		else
		{
			$ser[$type][$key] = $value;
		}

		$this->setSerializedCond($ser);
	}

	public function save()
	{
		BusinessRuleController::clearCache();
		return parent::save();
	}

	protected function insert()
	{
		if (is_null($this->isAnyRecord->get()))
		{
			$this->isAnyRecord = 1);
		}

		if (!$this->position->get())
		{
			$this->setLastPosition();
		}

		return parent::insert();
	}

	/**
	 * Creates array representation
	 *
	 * @return array
	 */
	protected static function transformArray($array, ARSchema $schema)
	{
		if (!empty($array['serializedCondition']))
		{
			$array['serializedCondition'] = unserialize($array['serializedCondition']);
		}

		return MultiLingualObject::transformArray($array, $schema);
	}

	public function toArray()
	{
		$array = parent::toArray();

		if ($this->subConditions)
		{
			foreach ($this->subConditions as $sub)
			{
				$array['sub'][] = $sub->toArray();
			}
		}

		if ($this->records)
		{
			foreach ($this->records as $record)
			{
				$array['records'][] = $record->toArray();
			}
		}

		return $array;
	}
}

?>