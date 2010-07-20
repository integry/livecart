<?php

ClassLoader::import('application.model.system.ActiveTreeNode');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.discount.DiscountAction');
ClassLoader::import('application.model.discount.DiscountConditionRecord');
ClassLoader::import('application.model.businessrule.BusinessRuleController');

/**
 *
 * @author Integry Systems
 * @package application.model.discount
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
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		parent::defineSchema($className);

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("isAnyRecord", ARBool::instance()));
		$schema->registerField(new ARField("isAllSubconditions", ARBool::instance()));
		$schema->registerField(new ARField("isActionCondition", ARBool::instance()));
		$schema->registerField(new ARField("isFinal", ARBool::instance()));
		$schema->registerField(new ARField("isReverse", ARBool::instance()));

		$schema->registerField(new ARField("conditionClass", ARVarchar::instance(80)));

		$schema->registerField(new ARField("recordCount", ARInteger::instance()));
		$schema->registerField(new ARField("validFrom", ARDateTime::instance()));
		$schema->registerField(new ARField("validTo", ARDateTime::instance()));
		$schema->registerField(new ARField("subTotal", ARInteger::instance()));
		$schema->registerField(new ARField("count", ARInteger::instance()));
		$schema->registerField(new ARField("comparisonType", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));
		$schema->registerField(new ARField("couponCode", ARVarchar::instance(100)));
		$schema->registerField(new ARField("couponLimitCount", ARInteger::instance()));
		$schema->registerField(new ARField("couponLimitType", ARInteger::instance()));
		$schema->registerField(new ARField("serializedCondition", ARText::instance()));
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
			$instance->isEnabled->set(true);
			$instance->lft->set(1);
			$instance->rgt->set(2);
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

	public function setSerializedValue($type, $key, $value)
	{
		$ser = $this->getSerializedCond();
		$ser[$type][$key] = $value;
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
			$this->isAnyRecord->set(1);
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