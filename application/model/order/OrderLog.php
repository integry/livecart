<?php

ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");

/**
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderLog extends ActiveRecordModel
{
	   const TYPE_ORDER = 0;
	   const TYPE_SHIPMENT = 1;
	   const TYPE_ORDERITEM = 2;
	   const TYPE_SHIPPINGADDRESS = 3;
	   const TYPE_BILLINGADDRESS = 4;

	   const ACTION_ADD = 0;
	   const ACTION_REMOVE = 1;
	   const ACTION_CHANGE = 2;
	   const ACTION_STATUSCHANGE = 3;
	   const ACTION_COUNTCHANGE = 4;
	   const ACTION_SHIPPINGSERVICECHANGE = 5;
	   const ACTION_SHIPMENTCHANGE = 6;
	   const ACTION_ORDER = 7;
	   const ACTION_CANCELEDCHANGE = 8;
	   const ACTION_REMOVED_WITH_SHIPMENT = 9;
	   const ACTION_NEW_DOWNLOADABLE_ITEM_ADDED = 10;
	   const ACTION_NEW_DOWNLOADABLE_ITEM_REMOVED = 11;

	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $userID", "user", "ID", "User;
		public $orderID", "order", "ID", 'CustomerOrder;

		public $type;
		public $action;
		public $time;
		public $oldTotal;
		public $newTotal;
		public $oldValue;
		public $newValue;
	}

	public static function getNewInstance($type, $action, $oldValue, $newValue, $oldTotal, $newTotal, User $user, CustomerOrder $order)
	{
		$instance = parent::getNewInstance(__CLASS__);

		$instance->user = $user);

		$instance->time = new ARSerializableDateTime());

		$instance->type = (int)$type);
		$instance->action = (int)$action);

		$instance->order = $order);

		$instance->oldTotal = $oldTotal);
		$instance->newTotal = $newTotal);

		$instance->oldValue = $oldValue);
		$instance->newValue = $newValue);

		return $instance;
	}

	public static function getInstanceById($id, $loadData = self::LOAD_DATA, $loadReferencedRecords = false)
	{
		return ActiveRecordModel::getInstanceById(__CLASS__, $id, $loadData, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	public static function getRecordSetByOrder(CustomerOrder $order, ARSelectFilter $filter = null, $loadReferencedRecords = false)
	{
		if(!$filter)
		{
			$filter = new ARSelectFilter();
		}

		$filter->mergeCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'orderID'), $order->getID()));
		$filter->setOrder(new ARFieldHandle(__CLASS__, 'time'), ARSelectFilter::ORDER_DESC);


		return self::getRecordSet($filter, $loadReferencedRecords);
	}
}

?>