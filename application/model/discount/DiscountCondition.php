<?php

ClassLoader::import('application.model.system.ActiveTreeNode');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.discount.DiscountAction');
ClassLoader::import('application.model.discount.DiscountConditionRecord');

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
		$schema->registerField(new ARField("isActionCondition", ARBool::instance()));

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
		$subConditions = self::loadSubConditions($set);
		$subConditions->add($this);
		self::loadConditionRecords($subConditions, 'DiscountConditionRecord');
		//self::loadConditionRecords($subConditions, array('Category' => array('DiscountConditionRecord'), 'Product', 'Manufacturer', 'User', 'UserGroup', 'DeliveryZone'));
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

			$lft = array_keys($lft);
			$rgt = array_keys($rgt);
			sort($lft);
			sort($rgt);

			// check if first and last child nodes are there
			if ((array_shift($lft) - 1 != $condition['lft']) || (array_pop($rgt) + 1 != $condition['rgt']))
			{
				return false;
			}

			// look for gaps in lft/rgt indexes
			$lft = array_flip($lft);
			foreach ($rgt as $r)
			{
				if (!isset($lft[$r + 1]))
				{
					return false;
				}
			}
		}

		// check if subconditions are valid
		$hasValid = false;
		foreach ($condition['sub'] as $sub)
		{
			if (self::hasAllSubConditions($sub))
			{
				$hasValid = true;
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

		return $hasValid;
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
		$conditions = ActiveRecordModel::getRecordSetArray('DiscountConditionRecord', $filter, array(__CLASS__, 'Category'));

		if (!$conditions)
		{
			return array();
		}

		// get retrieved record count for each condition
		$count = $records = array();
		foreach ($conditions as $condition)
		{
			$id = $condition[__CLASS__]['ID'];
			if (empty($count[$id]))
			{
				$count[$id] = 0;
			}
			$count[$id]++;
			$records[$id][] = $condition;
		}

		// check if the order contains all required records
		// For example, a condition may specifify that the order must
		// contain all listed products for the condition to apply
		$validConditions = array();
		foreach ($conditions as $key => $condition)
		{
			$cond = $condition[__CLASS__];

			// only require all records for products (not actual for users, etc.)
			if (!empty($records[$cond['ID']][0]))
			{
				$rec = $records[$cond['ID']][0];
				if (is_null($rec['productID']) && is_null($rec['manufacturerID']) && is_null($rec['categoryID']))
				{
					$conditions[$key]['isAnyRecord'] = $cond['isAnyRecord'] = true;
				}
			}

			if (!$cond['isAnyRecord'] && ($cond['recordCount'] > $count[$cond['ID']]))
			{
				unset($conditions[$key]);
			}
			else
			{
				$validConditions[$cond['ID']] = $cond;
			}
		}

		// filter out non-matching item count/subtotal conditions
		foreach ($validConditions as $condKey => $condition)
		{
			if (!is_null($condition['count']) || !is_null($condition['subTotal']))
			{
				$matchingItemCount = $matchingItemSubTotal = array();

				foreach ($records[$condition['ID']] as $record)
				{
					$items = array();
					if ($record['productID'])
					{
						$items = $order->getItemsByProduct(Product::getInstanceById($record['productID']));
					}
					elseif ($record['manufacturerID'])
					{
						foreach ($order->getShoppingCartItems() as $item)
						{
							if ($manufacturer = $item->product->get()->manufacturer->get())
							{
								$items[] = $item;
							}
						}
					}
					elseif ($record['categoryID'])
					{
						foreach ($order->getShoppingCartItems() as $item)
						{
							$category = $item->product->get()->category->get();

							if (($category->lft->get() >= $record['Category']['lft']) && ($category->rgt->get() <= $record['Category']['rgt']))
							{
								$items[] = $item;
							}
						}
					}
					else
					{
						$items = array();
					}

					foreach ($items as $item)
					{
						$matchingItemCount[$item->getID()] = $item->count->get();
						$matchingItemSubTotal[$item->getID()] = $item->getSubTotal($order->currency->get(), null, false);
					}
				}

				if (!is_null($condition['count']))
				{
					$expCount = $condition['count'];
					$foundCount = array_sum($matchingItemCount);
				}
				else
				{
					$expCount = $condition['subTotal'];
					$foundCount = array_sum($matchingItemSubTotal);
				}

				$compType = $condition['comparisonType'];

				if ((($foundCount > $expCount) && (self::COMPARE_LTEQ == $compType)) ||
					(($foundCount < $expCount) && (self::COMPARE_GTEQ == $compType)) ||
					(($foundCount != $expCount) && (self::COMPARE_EQ == $compType)) ||
					(($foundCount == $expCount) && (self::COMPARE_NE == $compType)))
				{
					unset($validConditions[$condKey]);
				}
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
			'userID' => $order->user->get() ? $order->user->get()->getID() : null,
			'deliveryZoneID' => $order->getDeliveryZone()->getID(),
		);

		$order->user->get()->load();
		if ($order->user->get() && ($userGroup = $order->user->get()->userGroup->get()))
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

		$cond = new EqualsCond(new ARFieldHandle(__CLASS__, 'isEnabled'), true);
		$cond->addAND(new OrChainCondition($conditions));

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

		return array_keys($ids);
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
		$cond->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'isActionCondition'), 0));
		self::applyDateCondition($cond);
		self::applyCouponCondition($cond, $order);

		$totalCond = self::applyOrderTotalCondition($order);
		$totalCond->addOr(self::applyOrderItemCountCondition($order));
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

	private static function applyCouponCondition(Condition $cond, CustomerOrder $order)
	{
		if ($order->getCoupons()->size())
		{
			return;
		}

		$handle = new ARFieldHandle(__CLASS__, 'couponCode');
		$couponCond = new IsNullCond($handle);

		foreach ($order->getCoupons() as $coupon)
		{
			$couponCond->addOr(new EqualsCond($handle, $coupon->couponCode->get()));
		}
		$couponCond->addOr(new EqualsCond($handle, ''));

		$cond->addAND($couponCond);
	}

	private static function applyOrderTotalCondition(CustomerOrder $order)
	{
		return self::applyRangeCondition($order, 'subTotal', $order->getSubTotal($order->currency->get(), false));
	}

	private static function applyOrderItemCountCondition(CustomerOrder $order)
	{
		return self::applyRangeCondition($order, 'count', $order->getShoppingCartItemCount());
	}

	private static function applyRangeCondition(CustomerOrder $order, $field, $amount)
	{
		$operators = array( '=' => self::COMPARE_EQ,
							'<=' => self::COMPARE_GTEQ,
							'>=' => self::COMPARE_LTEQ,
							'!=' => self::COMPARE_NE);

		$compHandle = new ARFieldHandle(__CLASS__, 'comparisonType');
		$totalHandle = new ARFieldHandle(__CLASS__, $field);

		$conditions = array();
		foreach ($operators as $operator => $code)
		{
			$c = new EqualsCond($compHandle, $code);
			$c->addAND(new OperatorCond($totalHandle, $amount, $operator));
			$conditions[] = $c;
		}

		$conditions[] = new MoreThanCond(new ARFieldHandle(__CLASS__, 'recordCount'), 0);

		$nullCond = new IsNullCond($compHandle);
		$nullCond->addOR(new OrChainCondition($conditions));

		return $nullCond;
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

	protected function insert()
	{
		$this->setLastPosition();
		return parent::insert();
	}

	/**
	 * Creates array representation
	 *
	 * @return array
	 */
	protected static function transformArray($array, ARSchema $schema)
	{
		return MultiLingualObject::transformArray($array, $schema);
	}

	public function toArray()
	{
		$array = parent::toArray();

		foreach ($this->subConditions as $sub)
		{
			$array['sub'][] = $sub->toArray();
		}

		foreach ($this->records as $record)
		{
			$array['records'][] = $record->toArray();
		}

		return $array;
	}
}

?>