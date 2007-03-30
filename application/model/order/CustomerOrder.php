<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");

/**
 * Represents customers order - products placed in shopping basket
 *
 * @package application.model.order
 */
class CustomerOrder extends ActiveRecordModel
{
	protected $orderedItems = array();
	
	protected $removedItems = array();
    
    protected static $instance = null;
    
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

		$schema->registerField(new ARField("sessionID", ARChar::instance(32)));
		$schema->registerField(new ARField("dateCreated", ARTimeStamp::instance()));
		$schema->registerField(new ARField("dateCompleted", ARTimeStamp::instance()));
		$schema->registerField(new ARField("status", ARInteger::instance(2)));
	}
	
	public static function getNewInstance(Session $session, User $user)	
	{
        $instance = parent::getNewInstance(__CLASS__);
        $instance->sessionID->set($session->getID());
        $instance->user->set($user->getID());     
        
        return $instance;   
    }
    
    public static function getInstance()
	{
        if (!self::$instance)
        {
            $instance = Session::getInstance()->getObject('CustomerOrder');
                
            if (!$instance)
            {
                $instance = self::getNewInstance(Session::getInstance(), User::getCurrentUser());
            }    
            
            self::$instance = $instance;
        }
                
        return self::$instance;
    }
    
    public function addProduct(Product $product, $count)
    {
        if ($count < 0)
        {
            throw new ApplicationException('Invalid product count (' . $count . ')');
        }
        
        if (0 == $count)
        {
            $this->removeProduct($product);
        }
        else
        {
            if (!$product->isAvailable())
            {
                throw new ApplicationException('Product is not available (' . $product->sku->get() . ')');
            }
            
            $this->orderedItems[] = OrderedItem::getNewInstance($this, $product, $count);
        }
    }
    
    public function removeProduct(Product $product)
    {
        $id = $product->getID();
        
        foreach ($this->orderedItems as $key => $item)
        {
            if ($item->product->getID() == $id)
            {
                $this->removedItems[] = $item;
                unset($this->orderedItems[$key]);
            }
        }    
    }
    
    public function save()
    {
        if ($this->orderedItems || $this->removedItems)
        {
            parent::save();
            
            foreach ($this->orderedItems as $item)
            {
                $item->save();
            }    
    
            foreach ($this->removedItems as $item)
            {
                $item->delete();
            }                
        }
    }    
    
    public function saveToSession()
    {
        Session::getInstance()->setObject('CustomerOrder', $this);
    }
    
    /**
     *  Merge OrderedItem instances of the same product into one instance
     */
    public function mergeItems()
    {
        
    }
    
    /**
     *  Loads ordered item/product info from database
     */
    protected function loadOrderedItems()
    {
        
    }
}
	
?>