<?php

ClassLoader::import('application.model.system.ActiveTreeNode');
ClassLoader::import('application.model.system.MultilingualObject');
ClassLoader::import('application.model.discount.DiscountAction');
ClassLoader::import('application.model.discount.DiscountConditionRecord');

class DiscountCondition extends ActiveTreeNode
{
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

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("description", ARArray::instance()));

		$schema->registerField(new ARField("couponCode", ARVarchar::instance(100)));
		$schema->registerField(new ARField("serializedCondition", ARText::instance()));
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

	private static function hasAllSubConditions($condition)
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

	private static function getConditionTreeFromArray($conditions)
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

			$idMap[$condition['parentNodeID']]['sub'][] = $condition;
		}

		// super-root node
		return isset($idMap[self::ROOT_ID]) ? $idMap[self::ROOT_ID] : null;
	}

	private static function getNonRecordConditions(CustomerOrder $order)
	{
		$cond = new EqualsCond(new ARFieldHandle('DiscountCondition', 'recordCount'), 0);
		self::applyConstraintConditions($cond);
		$filter = new ARSelectFilter($cond);
		return ActiveRecordModel::getRecordSetArray('DiscountCondition', $filter);
	}

	private static function getRecordConditions(CustomerOrder $order)
	{
		$conditions = ActiveRecordModel::getRecordSetArray('DiscountConditionRecord', new ARSelectFilter(self::getRecordCondition($order)), array('DiscountCondition'));

		if (!$conditions)
		{
			return array();
		}

		// count conditions
		$count = array();
		foreach ($conditions as $condition)
		{
			$id = $condition['DiscountCondition']['ID'];
			if ($count[$id])
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
			$cond = $condition['DiscountCondition'];
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
		self::applyConstraintConditions($cond);
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

	private static function applyConstraintConditions(Condition $cond)
	{
		$cond->addAND(new EqualsCond(new ARFieldHandle('DiscountCondition', 'isEnabled'), true));
		self::applyDateCondition($cond);
	}

	private static function applyDateCondition(Condition $cond)
	{
		$dateCondition = new EqualsCond(new ARFieldHandle('DiscountCondition', 'validFrom'), '0000-00-00');
		$dateCondition->addAND(new EqualsCond(new ARFieldHandle('DiscountCondition', 'validTo'), '0000-00-00'));
		$byDate = new EqualsOrLessCond(new ARFieldHandle('DiscountCondition', 'validFrom'), time());
		$byDate->addAND(new EqualsOrMoreCond(new ARFieldHandle('DiscountCondition', 'validTo'), time()));
		$dateCondition->addOr($byDate);
		$cond->addAND($dateCondition);
	}

	/*
	private static function isApplicableToOrder(CustomerOrder $order, array $condition)
	{
		$rules = unserialize($condition['serializedCondition']);
	}
	* */
}

?>