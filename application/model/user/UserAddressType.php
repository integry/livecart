<?php

ClassLoader::import('application/model/user/UserAddress');

/**
 * Abstract implementation of customer billing or shipping address. A customer can have several
 * billing and shipping addresses.
 *
 * @package application/model/user
 * @author Integry Systems <http://integry.com>
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

		public $ID;
		public $userID", "user", "ID", 'User;
		public $userAddressID", "userAddress", "ID", 'UserAddress;
	}

	public static function getNewInstance($className, User $user, UserAddress $userAddress)
	{
		$instance = parent::getNewInstance($className);
		$instance->user = $user;
		$instance->userAddress = $userAddress;
		return $instance;
	}

	public static function getUserAddress($className, $addressID, User $user)
	{
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle($className, 'ID'), $addressID));
		$f->andWhere(new EqualsCond(new ARFieldHandle($className, 'userID'), $user->getID()));
		$s = ActiveRecordModel::getRecordSet($className, $f, array('UserAddress'));

		if (!$s->size())
		{
			throw new ARNotFoundException($className, $addressID);
		}

		return $s->get(0);
	}

	public function save()
	{

		$this->load();

		$this->userAddress->load();
		$this->userAddress->save();
		return parent::save();
	}

	public function serialize()
	{
		return parent::serialize(array('userID'));
	}

	public function __destruct()
	{
		parent::destruct(array('userID', 'userAddressID'));
	}
}

?>
