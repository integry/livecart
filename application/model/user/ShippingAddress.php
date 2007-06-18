<?php

ClassLoader::import('application.model.user.UserAddressType');

/**
 * Customer shipping address
 *
 * @package application.model.user
 * @author Integry Systems <http://integry.com>
 */
class ShippingAddress extends UserAddressType
{
    /**
     * Define database schema
     */
	public static function defineSchema($className = __CLASS__)
	{
		parent::defineSchema($className);
	}
    
    public static function getNewInstance(User $user, UserAddress $userAddress)
    {
        return parent::getNewInstance(__CLASS__, $user, $userAddress);
    }    
    
    public static function getUserAddress($id, User $user)
    {
        return parent::getUserAddress(__class__, $id, $user);
    }
        
    public function save()
    {
        parent::save();
        
        $user = $this->user->get();
        $user->load();        
        if (!$user->defaultShippingAddress->get())
        {
            $user->defaultShippingAddress->set($this);
            $user->save();
        }
    }
}
	
?>