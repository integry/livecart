<?php

ClassLoader::import('application.model.user.UserAddress');

/**
 * Abstract implementation of customer billing or shipping address
 *
 * @package application.model.category
 */
abstract class UserAddressType extends ActiveRecordModel
{
    /**
     * Define database schema
     */
	public static function defineSchema($className)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "user", "ID", 'User', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userAddressID", "userAddress", "ID", 'UserAddress', ARInteger::instance()));
	}
    
    public static function getNewInstance($className, User $user, UserAddress $userAddress)
    {
        $instance = parent::getNewInstance($className);
        $instance->user->set($user);
        $instance->userAddress->set($userAddress);
        return $instance;
    }    
    
    public static function getUserAddress($className, $addressID, User $user)
    {
        $f = new ARSelectFilter();
        $f->setCondition(new EqualsCond(new ARFieldHandle($className, 'ID'), $addressID));
        $f->mergeCondition(new EqualsCond(new ARFieldHandle($className, 'userID'), $user->getID()));        
        $s = ActiveRecordModel::getRecordSet($className, $f, array('UserAddress'));
        
        if (!$s->size())
        {
            throw new ARNotFoundException($className, $addressID);
        }
        
        return $s->get(0);
    }
    
    public function serialize()
    {
        return parent::serialize(array('userID'));
    }
}
	
?>