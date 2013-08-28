<?php


/**
 * Customer-administration (support) message regarding an order
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderNote extends ActiveRecordModel
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $userID", "User", "ID", "User;
		public $orderID", "CustomerOrder", "ID", "CustomerOrder;

		public $isRead;
		public $isAdmin;
		public $time;
		public $text;
	}

	public static function getNewInstance(CustomerOrder $order, User $user)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->order = $order);
		$instance->user = $user);

		return $instance;
	}
}
?>