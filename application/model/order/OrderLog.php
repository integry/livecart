<?php
ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");

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
	   const ACTION_ORDER = 3;
	   const ACTION_STATUSCHANGE = 4;
	
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
		
	public static function getNewInstance($type, $action, $oldValue, $newValue, $oldTotal, $newTotal, User $user, CustomerOrder $order)	
	{
        $instance = parent::getNewInstance(__CLASS__);
        
		$instance->user->set($user);   
		
		$instance->time->set(new ARSerializableDateTime());
		
		$instance->type->set((int)$type);
		$instance->action->set((int)$action);
		
        $instance->order->set($order);
        
        $instance->oldTotal->set($oldTotal);
        $instance->newTotal->set($newTotal);
        
        $instance->oldValue->set($oldValue);
        $instance->newValue->set($newValue);
		
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
        
        $filter->getCondition()->addAND(new EqualsCond(new ARFieldHandle(__CLASS__, 'orderID'), $order->getID()));
        
        return self::getRecordSet($filter, $loadReferencedRecords);
    }
}
	
?>