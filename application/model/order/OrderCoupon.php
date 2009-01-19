<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.discount.DiscountCondition');

/**
 *
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderCoupon extends ActiveRecordModel
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("discountConditionID", "DiscountCondition", "ID", "DiscountCondition", ARInteger::instance()));
		$schema->registerField(new ARField("couponCode", ARVarchar::instance(255)));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order, $code)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->order->set($order);
		$instance->couponCode->set($code);
		return $instance;
	}

	public function isValid()
	{
		$cond = $this->discountCondition->get();
		if (!$cond || !$cond->couponLimitCount->get())
		{
			return true;
		}

		return $this->getUseCount() < $cond->couponLimitCount->get();
	}

	public function getUseCount()
	{
		$cond = $this->discountCondition->get();

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		if (($cond->couponLimitType->get() == DiscountCondition::COUPON_LIMIT_USER) && $this->order->get()->user->get())
		{
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->order->get()->user->get()->getID()));
		}

		return $cond->getRelatedRecordCount(__CLASS__, $f, array('CustomerOrder'));
	}
}

?>