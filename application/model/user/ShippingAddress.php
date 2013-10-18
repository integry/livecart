<?php


/**
 * Customer shipping address
 *
 * @package application/model/user
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
		
	public function save($forceOperation = null)
	{
		parent::save($forceOperation);
		
		$user = $this->user;
		$user->load();
		if (!$user->defaultShippingAddress || ($this === $user->defaultShippingAddress))
		{
			$user->defaultShippingAddress->set($this);
			$user->defaultShippingAddress->setAsModified();
			$user->save();
		}
	}
}
	
?>
