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
		$instance = new __CLASS__();
		$instance->order = $order;
		$instance->method = get_class($handler));
		return $instance;
	}

	public static function getInstanceByOrder(CustomerOrder $order)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, 'orderID'), $order->getID()));
		$s = self::getRecordSet(__CLASS__, $f);
		if ($s->size())
		{
			return $s->get(0);
		}
	}

	/*####################  Saving ####################*/

	public function deleteInstancesByOrder(CustomerOrder $order)
	{
		// remove other ExpressCheckout instances for this order
		$f = new ARDeleteFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle('ExpressCheckout', 'orderID'), $order->getID()));
		ActiveRecordModel::deleteRecordSet('ExpressCheckout', $f);
	}

	protected function insert()
	{
		$this->deleteInstancesByOrder($this->order->get());

		return parent::insert();
	}

	/*####################  Get related objects ####################*/

	public function getHandler(TransactionDetails $transaction = null)
	{
		$handler = $this->getApplication()->getExpressPaymentHandler($this->method->get(), $transaction);
		$handler->setData(unserialize($this->paymentData->get()));
		return $handler;
	}

	public function getTransactionDetails()
	{
		return $this->getHandler()->getDetails();
	}
}

?>