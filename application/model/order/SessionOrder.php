<?php

ClassLoader::import('application.model.user.SessionUser');
ClassLoader::import('application.model.order.CustomerOrder');

class SessionOrder
{
	/**
	 * Get CustomerOrder instance from session
	 *
	 * @return CustomerOrder
	 */
    public static function getOrder()
    {
        $session = new Session();
        
        $id = $session->get('CustomerOrder');
        if ($id)
        {
            try
            {
				$instance = CustomerOrder::getInstanceById($id, true);
                if (!$instance->getOrderedItems())
                {
                    $instance->loadItems();
                }
				$instance->isSyncedToSession = true;					
			}
			catch (ARNotFoundException $e)
			{
				unset($instance);	
			}
        }
        
        if (!isset($instance))
        {
            $instance = CustomerOrder::getNewInstance(User::getNewInstance(0));
            $instance->user->set(NULL);
        }    

        if (!$instance->user->get() && SessionUser::getUser()->getID() > 0)
        {
            $instance->user->set(SessionUser::getUser());
            $instance->save();
        }
                
        if ($instance->isFinalized->get())
        {
            $session->unsetValue('CustomerOrder');
            return self::getOrder();
        }
                
        self::setOrder($instance);
                
        return $instance;
    }
    
	public static function setOrder(CustomerOrder $order)
	{
		$session = new Session();
		$session->set('CustomerOrder', $order->getID());
	}

	public static function save(CustomerOrder $order)
	{
		$order->save();
		self::setOrder($order);
	}

	public static function destroy()
	{
		$session = new Session();
		$session->unsetValue('CustomerOrder');		
	}
}

?>