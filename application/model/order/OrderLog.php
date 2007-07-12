<?php
ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");

class OrderLog extends ActiveRecordModel
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
		$schema->registerField(new ARForeignKeyField("userID", "user", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "order", "ID", 'CustomerOrder', ARInteger::instance()));

		$schema->registerField(new ARField("type", ARInteger::instance()));
		$schema->registerField(new ARField("action", ARInteger::instance()));
		$schema->registerField(new ARField("time", ARDateTime::instance()));
		$schema->registerField(new ARField("oldTotal", ARFloat::instance()));
		$schema->registerField(new ARField("newTotal", ARFloat::instance()));
		$schema->registerField(new ARField("oldValue", ARText::instance()));
		$schema->registerField(new ARField("newValue", ARText::instance()));
	}
		
	public static function getNewInstance(User $user, CustomerOrder $oldOrder, CustomerOrder $newOrder, $type, $action)	
	{
        $instance = parent::getNewInstance(__CLASS__);
        
		$instance->user->set($user);   
		$instance->time->set(new ARSerializableDateTime());
		$instance->type->set($type);
		$instance->action->set($action);
        $instance->oldTotal->set($oldOrder->totalAmount->get());
        $instance->newTotal->set($newOrder->totalAmount->get());
		
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
}
	
?>