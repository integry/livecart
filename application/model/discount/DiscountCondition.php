<?php

ClassLoader::import('application.model.system.ActiveTreeNode');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.discount.DiscountAction');
ClassLoader::import('application.model.discount.DiscountConditionRecord');

class DiscountCondition extends ActiveTreeNode
{
	// equal
	const COMPARE_EQ = 0;

	// less than or equal
	const COMPARE_LTEQ = 1;

	// greater than or equal
	const COMPARE_GTEQ = 2;

	// not equal
	const COMPARE_NE = 3;

	private $records = array();

	private $subConditions = array();

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
		$schema->registerField(new ARField("serializedCondition", ARText::instance()));
	}

	public function loadAll()
	{
		$set = new ARSet();
		$set->add($this);
		self::loadConditionRecords($set);
		self::loadSubConditions($set);
	}

	public function registerRecord(DiscountConditionRecord $record)
	{
		$this->records[$record->getID()] = $record;
	}

	public function registerSubCondition(DiscountCondition $condition)
	{
		$this->subConditions[$condition->getID()] = $condition;
	}

	public function isProductMatching(Product $product)
	{
		// no records defined
		if (!$this->recordCount->get())
		{
			return true;
		}

		$isMatching = false;

		foreach ($this->records as $record)
		{
			$isMatching = ($record->product->get() && ($record->product->get()->getID() == $product->getID()))
						  || ($record->manufacturer->get() && $product->manufacturer->get() && ($record->manufacturer->get()->getID() == $product->manufacturer->get()->getID()))
						  || ($record->category->get() && $product->belongsTo($record->category->get()));

			if ($isMatching)
			{
				break;
			}
		}

		if ($this->hasSubConditions())
		{
			foreach ($this->subConditions as $subCondition)
			{
				$isMatching = $subCondition->isProductMatching($product);

				if (!$isMatching && $this->isAllSubconditions->get())
				{
					return false;
				}
			}
		}

		return $isMatching;
	}

	public function hasSubConditions()
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

	public static function getNewInstance(DiscountCondition $parentCondition = null)
	{
		if (!$parentCondition)
		{
			$parentCondition = self::getRootNode();
		}

		return parent::getNewInstance(__CLASS__, $parentCondition);
	}

	public static function getOrderDiscountConditions(CustomerOrder $order)
	{
		$tree = self::getConditionTreeFromArray(array_merge(self::getRecordConditions($order), self::getNonRecordConditions($order)));

		if (!$tree || empty($tree['sub']))
		{
			return array();
		}

		foreach ($tree['sub'] as $key => $condition)
		{
			if (!self::hasAllSubConditions($condition))
			{
				unset($tree['sub'][$key]);
			}
		}

		return $tree['sub'];
	}

	public static function loadConditionRecords(ARSet $conditionSet)
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
		foreach (ActiveRecordModel::getRecordSet('DiscountConditionRecord', $f, array('Category')) as $record)
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
			return null;
		}

		$f = new ARSelectFilter(Condition::mergeFromArray($cond, true));

		foreach (ActiveRecordModel::getRecordSet(__CLASS__, $f) as $condition)
		{
			$condition->parentNode->get()->registerSubCondition($condition);
		}
	}

	private static function hasAllSubConditions(array $condition)
	{
		// no subconditions created
		if (1 == ($condition['rgt'] - $condition['lft']))
		{
			return true;
		}

		// no subconditions retrieved
		if (empty($condition['sub']))
		{
			return false;
		}

		// check if there are no missing subconditions
		if ($condition['isAllSubconditions'])
		{
			$lft = $rgt = array();
			foreach ($condition['sub'] as $subCond)
			{
				$lft[$subCond['lft']] = true;
				$rgt[$subCond['rgt']] = true;
			}

			ksort($lft);
			ksort($rgt);

			// check if first and last child nodes are there
			if ((array_shift($lft) - 1 != $condition['lft']) || (array_pop($rgt) + 1 != $condition['rgt']))
			{
				return false;
			}

			// look for gaps in lft/rgt indexes
			foreach ($rgt as $r)
			{
				if (!isset($lft[$r + 1]))
				{
					return false;
				}
			}
		}

		// check if subconditions are valid
		foreach ($condition['sub'] as $sub)
		{
			if (self::hasAllSubConditions($sub))
			{
				if (!$condition['isAllSubconditions'])
				{
					return true;
				}
			}
			else
			{
				if ($condition['isAllSubconditions'])
				{
					return false;
				}
			}
		}

		return !$condition['isAllSubconditions'];
	}

	private static function getConditionTreeFromArray(array $conditions)
	{
		$idMap = array();
		foreach ($conditions as &$condition)
		{
			$idMap[$condition['ID']] =& $condition;
		}

		foreach ($conditions as &$condition)
		{
			if (!$condition['parentNodeID'] || !isset($idMap[$condition['parentNodeID']]))
			{
				continue;
			}

			$idMap[$condition['parentNodeID']]['sub'][] =& $condition;
		}

		// super-root node
		return isset($idMap[self::ROOT_ID]) ? $idMap[self::ROOT_ID] : null;
	}

	private static function getNonRecordConditions(CustomerOrder $order)
	{
		$cond = new EqualsCond(new ARFieldHandle(__CLASS__, 'recordCount'), 0);
		self::applyConstraintConditions($cond, $order);
		$filter = new ARSelectFilter($cond);
		$filter->setOrder(new ARFieldHandle(__CLASS__, 'position'), 'ASC');
		return ActiveRecordModel::getRecordSetArray(__CLASS__, $filter);
	}

	private static function getRecordConditions(CustomerOrder $order)
	{
		$filter = new ARSelectFilter(self::getRecordCondition($order));
		$filter->setOrder(new ARFieldHandle(__CLASS__, 'position'), 'ASC');
		$conditions = ActiveRecordModel::getRecordSetArray('DiscountConditionRecord', $filter, array(__CLASS__));

		if (!$conditions)
		{
			return array();
		}

		// count conditions
		$count = array();
		foreach ($conditions as $condition)
		{
			$id = $condition[__CLASS__]['ID'];
			if (empty($count[$id]))
			{
				$count[$id] = 0;
			}
			$count[$id]++;
		}

		// check if the order contains all required records
		// For example, a condition may specifify that the order must
		// contain all listed products for the condition to apply
		$validConditions = array();
		foreach ($conditions as $key => $condition)
		{
			$cond = $condition[__CLASS__];
			if (!$cond['isAnyRecord'] && ($cond['recordCount'] > $count[$cond['ID']]))
			{
				unset($conditions[$key]);
			}
			else
			{
				$validConditions[$cond['ID']] = $cond;
			}
		}

		return $validConditions;
	}

	private static function getRecordCondition(CustomerOrder $order)
	{
		$records = array(
			'productID' => self::getOrderProductIDs($order),
			'categoryID' => self::getOrderCategoryIDs($order),
			'manufacturerID' => self::getOrderManufacturerIDs($order),
			'userID' => $order->user->get()->getID(),
			'deliveryZoneID' => $order->getDeliveryZone()->getID(),
		);

		if ($userGroup = $order->user->get()->userGroup->get())
		{
			$records['userGroupID'] = $userGroup->getID();
		}

		$conditions = array();
		foreach ($records as $field => $ids)
		{
			$handle = new ARFieldHandle('DiscountConditionRecord', $field);
			if (is_numeric($ids))
			{
				$cond = new EqualsCond($handle, $ids);
			}
			else if (!$ids)
			{
				continue;
			}
			else
			{
				$cond = new INCond($handle, $ids);
			}

			$conditions[] = $cond;
		}

		$cond = Condition::mergeFromArray($conditions, true);
		self::applyConstraintConditions($cond, $order);
		return $cond;
	}

	private static function getOrderProductIDs(CustomerOrder $order)
	{
		$ids = array();

		foreach ($order->getOrderedItems() as $item)
		{
			$ids[] = $item->product->get()->getID();
		}

		return $ids;
	}

	private static function getOrderManufacturerIDs(CustomerOrder $order)
	{
		$ids = array();

		foreach ($order->getOrderedItems() as $item)
		{
			$manufacturer = $item->product->get()->manufacturer->get();
			if ($manufacturer)
			{
				$ids[$manufacturer->getID()] = true;
			}
		}

		return $ids;
	}

	private static function getOrderCategoryIDs(CustomerOrder $order)
	{
		$conditions = array();
		foreach ($order->getOrderedItems() as $item)
		{
			$category = $item->product->get()->category->get();
			$conditions[$category->getID()] = $category->getPathNodeCondition();
		}

		if (!$conditions)
		{
			return array();
		}

		$query = new ARSelectQueryBuilder();
		$query->includeTable('Category');
		$query->addField('ID');
		$query->setFilter(new ARSelectFilter(Condition::mergeFromArray($conditions, true)));

		return $query;
	}

	private static function applyConstraintConditions(Condition $cond, CustomerOrder $order)
	{
		$cond->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'isEnabled'), true));
		self::applyDateCondition($cond);

		$totalCond = self::applyOrderTotalCondition($cond, $order);
		$totalCond->addOr(self::applyOrderItemCountCondition($cond, $order));
		$cond->addAND($totalCond);
	}

	private static function applyDateCondition(Condition $cond)
	{
		$dateCondition = new EqualsCond(new ARFieldHandle(__CLASS__, 'validFrom'), '0000-00-00');
		$dateCondition->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'validTo'), '0000-00-00'));
		$byDate = new EqualsOrLessCond(new ARFieldHandle(__CLASS__, 'validFrom'), time());
		$byDate->addAND(new EqualsOrMoreCond(new ARFieldHandle(__CLASS__, 'validTo'), time()));
		$dateCondition->addOr($byDate);
		$cond->addAND($dateCondition);
	}

	private static function applyOrderTotalCondition(Condition $cond, CustomerOrder $order)
	{
		return self::applyRangeCondition($cond, $order, 'subTotal', $order->getSubTotal($order->currency->get(), false));
	}

	private static function applyOrderItemCountCondition(Condition $cond, CustomerOrder $order)
	{
		return self::applyRangeCondition($cond, $order, 'count', $order->getShoppingCartItemCount());
	}

	private static function applyRangeCondition(Condition $cond, CustomerOrder $order, $field, $amount)
	{
		$compHandle = new ARFieldHandle(__CLASS__, 'comparisonType');
		$nullCond = new IsNullCond($compHandle);

		$operators = array( '=' => self::COMPARE_EQ,
							'<=' => self::COMPARE_GTEQ,
							'>=' => self::COMPARE_LTEQ,
							'!=' => self::COMPARE_NE);

		$totalHandle = new ARFieldHandle(__CLASS__, $field);

		$conditions = array();
		foreach ($operators as $operator => $code)
		{
			$c = new EqualsCond($compHandle, $code);
			$c->addAND(new OperatorCond($totalHandle, $amount, $operator));
			$conditions[] = $c;
		}

		$totalCond = Condition::mergeFromArray($conditions, true);
		$totalCond->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'recordCount'), 0));

		$nullCond->addOR($totalCond);

		return $nullCond;
	}

	protected function insert()
	{
		$this->setLastPosition();
		return parent::insert();
	}
}

?>