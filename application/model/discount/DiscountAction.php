<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.order.OrderDiscount');

class DiscountAction extends ActiveRecordModel
{
	const TYPE_ORDER_DISCOUNT = 0;
	const TYPE_ITEM_DISCOUNT = 1;

	const MEASURE_PERCENT = 0;
	const MEASURE_AMOUNT = 1;

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

		$schema->registerField(new ARField("amountMeasure", ARInteger::instance()));
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

		return ActiveRecordModel::getRecordSet(__CLASS__, $f);
	}

	public function isOrderDiscount()
	{
		return self::TYPE_ORDER_DISCOUNT == $this->type->get();
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

		$discountAmount = $this->getDiscountAmount($order->getSubTotal($order->currency->get()));

		$discount = OrderDiscount::getNewInstance($order);
		$discount->amount->set($discountAmount);

		return $discount;
	}

	public function getDiscountAmount($price)
	{
		return ($this->amountMeasure->get() == self::MEASURE_PERCENT) ? $price * ($this->amount->get() / 100) : $this->amount->get();
	}

	protected function insert()
	{
	  	$this->setLastPosition();
		return parent::insert();
	}
}

?>