<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.discount.DiscountActionSet');
ClassLoader::import('application.model.order.OrderDiscount');
ClassLoader::import('application.model.businessrule.interface.*');

/**
 *
 * @author Integry Systems
 * @package application.model.discount
 */
class DiscountAction extends ActiveRecordModel
{
	const TYPE_ORDER_DISCOUNT = 0;
	const TYPE_ITEM_DISCOUNT = 1;
	const TYPE_CUSTOM_DISCOUNT = 5;

	/**
	 * Action for discount condition (define the actual discount)
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("conditionID", "DiscountCondition", "ID", "DiscountCondition", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("actionConditionID", "DiscountCondition", "ID", "DiscountCondition", ARInteger::instance()));

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance()));

		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("discountStep", ARInteger::instance()));
		$schema->registerField(new ARField("discountLimit", ARInteger::instance()));

		$schema->registerField(new ARField("amount", ARFloat::instance()));
		$schema->registerField(new ARField("actionClass", ARVarchar::instance(80)));
	}

	public static function getNewInstance(DiscountCondition $condition, $className = 'RuleActionPercentageDiscount')
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->condition->set($condition);
		$instance->actionClass->set($className);

		return $instance;
	}

	public static function getByConditions(array $conditions)
	{
		$ids = array();
		foreach ($conditions as $condition)
		{
			$ids[] = $condition['ID'];
		}

		if (!$ids)
		{
			return new ARSet();
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle(__CLASS__, 'conditionID'), $ids));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle(__CLASS__, 'position'));

		$actions = ActiveRecordModel::getRecordSet(__CLASS__, $f, array('DiscountCondition', 'DiscountCondition_ActionCondition'));

		// @todo: actionConditions are not loaded
		foreach ($actions as $action)
		{
			if ($action->actionCondition->get())
			{
				$action->actionCondition->get()->load();
			}
		}

		// load records for action condition
		$actionConditions = new ARSet();
		foreach ($actions as $action)
		{
			if ($action->actionCondition->get())
			{
				$actionConditions->add($action->actionCondition->get());
			}
		}

		if ($actionConditions->size())
		{
			DiscountCondition::loadConditionRecords($actionConditions);
		}

		return $actions;
	}

	public function getActionClass()
	{
		$class = $this->actionClass->get();
		if (!class_exists($class, false))
		{
			$this->loadActionRuleClass($class);
		}

		return $class;
	}

	private function loadActionRuleClass($className)
	{
		ClassLoader::import('application.model.businessrule.action.' . $className);
		if (!class_exists($className, false))
		{
			foreach (self::getApplication()->getPlugins('businessrule/action/' . $className) as $plugin)
			{
				include_once $plugin['path'];
			}
		}

		return $className;
	}

	public function isOrderDiscount()
	{
		return (self::TYPE_ORDER_DISCOUNT == $this->type->get()) && !$this->getRuleAction()->isItemDiscount();
	}

	public function isItemDiscount()
	{
		return (self::TYPE_ITEM_DISCOUNT == $this->type->get()) || $this->getRuleAction()->isItemDiscount();
	}

	public function isItemApplicable(OrderedItem $item)
	{
		if (!$this->actionCondition->get())
		{
			return true;
		}

		return $this->actionCondition->get()->isProductMatching($item->product->get());
	}

	public function getRuleAction()
	{
		if (is_null($this->ruleAction))
		{
			$class = $this->getActionClass();
			$this->ruleAction = new $class($this);
		}

		return $this->ruleAction;
	}

	protected function insert()
	{
		$this->setLastPosition();
		return parent::insert();
	}
}

?>