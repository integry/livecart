<?php

ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.user.UserAddress");

/**
 * Express checkout data container
 *
 * @package application.model.user
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
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("addressID", "address", "ID", 'UserAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "order", "ID", 'CustomerOrder', ARInteger::instance()));

		$schema->registerField(new ARField("token", ARVarchar::instance(100)));
		$schema->registerField(new ARField("method", ARVarchar::instance(40)));
	}    
	
	public static function getNewInstance(CustomerOrder $order, ExpressPayment $handler)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->order->set($order);
		$instance->method->set(get_class($handler));
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
	
	public function getTransactionDetails()
	{
        $handler = $this->getApplication()->getExpressPaymentHandler($this->method->get());
        $handler->setToken($this->token->get());
        return $handler->getDetails();
    }
	
	protected function insert()
	{
        // remove other ExpressCheckout instances for this order
        $f = new ARDeleteFilter();
        $f->setCondition(new EqualsCond(new ARFieldHandle('ExpressCheckout', 'orderID'), $this->order->get()->getID()));
        ActiveRecordModel::deleteRecordSet('ExpressCheckout', $f);
        
        return parent::insert();
    }
}

?>