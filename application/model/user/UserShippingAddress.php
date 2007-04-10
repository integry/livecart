<?php

/**
 * Customer shipping address
 *
 * @package application.model.user
 */
class UserShippingAddress extends UserAddressType
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
}
	
?>