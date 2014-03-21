<?php


/**
 * Express checkout data container
 *
 * @package application/model/user
 * @author Integry Systems <http://integry.com>
 */
class ExpressCheckout extends ActiveRecordModel
{
	/**
	 * Define database schema
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $addressID", "address", "ID", 'UserAddress;
		public $orderID", "order", "ID", 'CustomerOrder;

		public $method;
		public $paymentData;
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order, ExpressPayment $handler)
	{
		$instance = new self();
		$instance->order = $order;
		$instance->method = get_class($handler));
		return $instance;
	}

	public static function getInstanceByorderBy(CustomerOrder $order)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, 'orderID'), $order->getID()));
		$s = self::getRecordSet(__CLASS__, $f);
		if ($s->count())
		{
			return $s->shift();
		}
	}

	/*####################  Saving ####################*/

	public function deleteInstancesByorderBy(CustomerOrder $order)
	{
		// remove other ExpressCheckout instances for this order
		$f = new ARDeleteFilter();
		$f->setCondition('ExpressCheckout.orderID = :ExpressCheckout.orderID:', array('ExpressCheckout.orderID' => $order->getID()));
		ActiveRecordModel::deleteRecordSet('ExpressCheckout', $f);
	}

	public function beforeCreate()
	{
		$this->deleteInstancesByorderBy($this->order);


	}

	/*####################  Get related objects ####################*/

	public function getHandler(TransactionDetails $transaction = null)
	{
		$handler = $this->getApplication()->getExpressPaymentHandler($this->method, $transaction);
		$handler->setData(unserialize($this->paymentData));
		return $handler;
	}

	public function getTransactionDetails()
	{
		return $this->getHandler()->getDetails();
	}
}

?>