<?php

ClassLoader::import('application.model.user.UserAddressType');

/**
 * Customer billing address
 *
 * @package application.model.user
 * @author Integry Systems <http://integry.com>
 */
class BillingAddress extends UserAddressType
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
		
		$user = $this->user->get();
		$user->load();	 
		if (!$user->defaultBillingAddress->get())
		{
			$user->defaultBillingAddress->set($this);
			$user->save();
		}
	}	
}
	
?>