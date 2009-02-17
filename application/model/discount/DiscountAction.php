<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.order.OrderDiscount');

class DiscountAction extends ActiveRecordModel
{
	const TYPE_ORDER_DISCOUNT = 0;
	const TYPE_ITEM_DISCOUNT = 1;
	const TYPE_CUSTOM_DISCOUNT = 5;

	const ACTION_PERCENT = 0;
	const ACTION_AMOUNT = 1;
	const ACTION_DISABLE_CHECKOUT = 2;
	const ACTION_SURCHARGE_PERCENT = 3;
	const ACTION_SURCHARGE_AMOUNT = 4;

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

		$schema->registerField(new ARField("actionType", ARInteger::instance()));
		$schema->registerField(new ARField("amount", ARFloat::instance()));
	}

	public static function getNewInstance(DiscountCondition $condition)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->condition->set($condition);

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

	public function isOrderDiscount()
	{
		return self::TYPE_ORDER_DISCOUNT == $this->type->get();
	}

	public function isItemDiscount()
	{
		return (self::TYPE_ITEM_DISCOUNT == $this->type->get()) || (self::ACTION_PERCENT == $this->actionType->get()) || (self::ACTION_SURCHARGE_PERCENT == $this->actionType->get());
	}

	public function isFixedAmount()
	{
		return (self::ACTION_AMOUNT == $this->actionType->get()) || (self::ACTION_SURCHARGE_AMOUNT == $this->actionType->get());
	}

	public function isItemApplicable(OrderedItem $item)
	{
		if (!$this->actionCondition->get())
		{
			return true;
		}

		return $this->actionCondition->get()->isProductMatching($item->product->get());
	}

	public function getOrderDiscount(CustomerOrder $order)
	{
		if (!$this->isOrderDiscount())
		{
			return null;
		}

		$discountAmount = $this->getDiscountAmount($order->getSubTotal());

		$discount = OrderDiscount::getNewInstance($order);
		$discount->amount->set($discountAmount);

		return $discount;
	}

	public function getDiscountAmount($price)
	{
		switch ($this->actionType->get())
		{
			case self::ACTION_PERCENT:
				return $price * ($this->amount->get() / 100);

			case self::ACTION_SURCHARGE_PERCENT:
				return $price * ($this->amount->get() / 100) * -1;

			case self::ACTION_AMOUNT:
				return $this->amount->get();

			case self::ACTION_SURCHARGE_AMOUNT:
				return $this->amount->get() * -1;
		}
	}

	protected function insert()
	{
	  	$this->setLastPosition();
		return parent::insert();
	}
}

?>