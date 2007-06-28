<?php

ClassLoader::import("application.model.product.Product");

/**
 * Represents a financial/monetary transaction, which can be:
 *    
 *      a) customers payment for ordered items
 *      b) capture transaction to request authorized funds
 *      c) void transaction to cancel an earlier transaction
 *
 * The transaction must be assigned to a concrete CustomerOrder
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>   
 */
class Transaction extends ActiveRecordModel
{
    const TYPE_SALE = 0;
    const TYPE_AUTH = 1;
    const TYPE_CAPTURE = 2;
    const TYPE_VOID = 3;
           
    /**
     *  Instance of credit card handler object
     */
    private $handler;
            
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
		$schema->registerField(new ARForeignKeyField("parentTransactionID", "Transaction", "ID", "Transaction", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("orderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("currencyID", "currency", "ID", 'Currency', ARChar::instance(3)));
		
		$schema->registerField(new ARField("amount", ARFloat::instance()));
		$schema->registerField(new ARField("time", ARDateTime::instance()));
		$schema->registerField(new ARField("method", ARVarchar::instance(40)));
		$schema->registerField(new ARField("gatewayTransactionID", ARVarchar::instance(40)));
		$schema->registerField(new ARField("type", ARInteger::instance()));
		$schema->registerField(new ARField("isCompleted", ARBool::instance()));

		$schema->registerField(new ARField("isCreditCard", ARBool::instance()));		
        $schema->registerField(new ARField("ccExpiryYear", ARInteger::instance()));
        $schema->registerField(new ARField("ccExpiryMonth", ARInteger::instance()));
        $schema->registerField(new ARField("ccLastDigits", ARInteger::instance()));
	}
	
	public static function getNewInstance(CustomerOrder $order, TransactionResult $result)
	{
        $instance = parent::getNewInstance(__CLASS__);
        $instance->order->set($order);
        
        foreach (array('amount', 'gatewayTransactionID') as $field)
        {
            $instance->$field->set($result->$field->get());
        }
        
        $instance->currency->set(Currency::getInstanceById($result->currency->get()));
        
        // different currency than initial order currency?
        $amount = $result->amount->get();
        if ($order->currency->get()->getID() != $result->currency->get())
        {
            $amount = $order->currency->get()->convertAmount($instance->currency->get(), $amount);
        }
        
        $instance->type->set($result->getTransactionType());
        
        if ($result->isCaptured())
        {            
            $instance->isCompleted->set(true);
            $order->capturedAmount->set($order->capturedAmount->get() + $amount);
        }
        
        return $instance;   
    }
    
    public static function transformArray($array)
    {
		$array = parent::transformArray($array, __CLASS__);
        
        try
        {
            $array['formattedAmount'] = Currency::getInstanceByID($array['Currency']['ID'])->getFormattedPrice($array['amount']);
        }
        catch (ARNotFoundException $e)
        {            
        }
        
        $array['methodName'] = Store::getInstance()->getLocaleInstance()->translator()->translate($array['method']);
        
        return $array;        
    }
    
    public static function loadHandlerClass($className, $isCreditCard)
    {
        if (!class_exists($className, false))
        {
            ClassLoader::import('library.payment.method.' . ($isCreditCard ? 'cc.' : '') . $className . '.' . $className);
        }
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        
        $array['isVoidable'] = $this->isVoidable();
        
        return $array;
    }    
    
    public function setHandler(TransactionPayment $handler)
    {
        $this->handler = $handler;
    }
    
    public function isVoidable()
    {
        self::loadHandlerClass($this->method->get(), $this->isCreditCard->get());
        return call_user_func(array($this->method->get(), 'isVoidable'));
    }
    
    protected function insert()
    {
        $this->order->get()->save();
        
        if ($this->handler instanceof CreditCardPayment)
        {
            $this->isCreditCard->set(true);
            $this->ccExpiryMonth->set($this->handler->getExpirationMonth());
            $this->ccExpiryYear->set($this->handler->getExpirationYear());
                        
            // only the last 5 digits of credit card number are stored
            $this->ccLastDigits->set(substr($this->handler->getCardNumber(), -5));
        }
        
        if ($this->handler)
        {
            $this->method->set(get_class($this->handler));
        }
        
        return parent::insert();
    }
}

?>