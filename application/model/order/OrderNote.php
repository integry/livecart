<?php

ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.user.User");

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
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));

		$schema->registerField(new ARField("isRead", ARBool::instance()));
		$schema->registerField(new ARField("isAdmin", ARBool::instance()));
		$schema->registerField(new ARField("time", ARDateTime::instance()));
		$schema->registerField(new ARField("text", ARText::instance()));
	}
	
	public static function getNewInstance(CustomerOrder $order, User $user)	
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->order->set($order);
		$instance->user->set($user);
		
		return $instance;
	}
}	
?>