<?php


/**
 *
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class OrderCoupon extends ActiveRecordModel
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */



		public $ID;
		public $orderID", "CustomerOrder", "ID", "CustomerOrder;
		public $discountConditionID", "DiscountCondition", "ID", "DiscountCondition;
		public $couponCode;
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order, $code)
	{
		$instance = new self();
		$instance->order = $order;
		$instance->couponCode = $code;
		return $instance;
	}

	public function isValid()
	{
		$cond = $this->discountCondition;
		if (!$cond || !$cond->couponLimitCount)
		{
			return true;
		}

		return $this->getUseCount() < $cond->couponLimitCount;
	}

	public function getUseCount()
	{
		$cond = $this->discountCondition;

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		if (($cond->couponLimitType == DiscountCondition::COUPON_LIMIT_USER) && $this->order->user)
		{
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->order->user->getID()));
		}

		return $cond->getRelatedRecordCount(__CLASS__, $f, array('CustomerOrder'));
	}
}

?>