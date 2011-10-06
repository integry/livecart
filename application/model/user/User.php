<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.user.BillingAddress");
ClassLoader::import("application.model.user.ShippingAddress");
ClassLoader::import("application.model.user.UserGroup");
ClassLoader::import("application.model.eav.EavAble");
ClassLoader::import("application.model.eav.EavObject");
ClassLoader::import("application.model.user.UserAddress");

/**
 * Store user logic (including frontend and backend), including authorization and access control checking
 *
 * @package application.model.user
 * @author Integry Systems <http://integry.com>
 */
class User extends ActiveRecordModel implements EavAble
{
	/**
	 * ID of anonymous user that is not authorized
	 *
	 */
	const ANONYMOUS_USER_ID = NULL;

	private $newPassword;

	public $grantedRoles = array();

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("User");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("defaultShippingAddressID", "defaultShippingAddress", "ID", 'ShippingAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("defaultBillingAddressID", "defaultBillingAddress", "ID", 'BillingAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userGroupID", "UserGroup", "ID", "UserGroup", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);

		$schema->registerField(new ARField("locale", ARVarchar::instance(4)));
		$schema->registerField(new ARField("email", ARVarchar::instance(60)));
		$schema->registerField(new ARField("password", ARVarchar::instance(32)));
		$schema->registerField(new ARField("firstName", ARVarchar::instance(60)));
		$schema->registerField(new ARField("lastName", ARVarchar::instance(60)));
		$schema->registerField(new ARField("companyName", ARVarchar::instance(60)));
		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("preferences", ARArray::instance()));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Create new user
	 *
	 * @param string $email Email
	 * @param string $password Password
	 * @param UserGroup $userGroup User group
	 *
	 * @return User
	 */
	public static function getNewInstance($email, $password = null, UserGroup $userGroup = null)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->email->set($email);
		$instance->dateCreated->set(new ARSerializableDateTime());

		if($userGroup)
		{
			$instance->userGroup->set($userGroup);
		}

		if($password)
		{
			$instance->setPassword($password);
		}

		return $instance;
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return User
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = array('UserGroup'), $data = array())
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}

	/**
	 * Load users set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/*####################  Instance retrieval ####################*/

	/**
	 * Load users that belong to the specified group
	 *
	 * @param DeliveryZone $deliveryZone
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSetByGroup(UserGroup $userGroup = null, ARSelectFilter $filter = null, $loadReferencedRecords = array('UserGroup'))
	{
		if(!$filter)
		{
			$filter = new ARSelectFilter();
		}

		if(!$userGroup)
		{
			$filter->mergeCondition(new IsNullCond(new ARFieldHandle(__CLASS__, "userGroupID")));
		}
		else
		{
			$filter->mergeCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "userGroupID"), $userGroup->getID()));
		}

		return self::getRecordSet($filter, $loadReferencedRecords);
	}

	/**
	 * Gets an instance of user by using login information
	 *
	 * @param string $email
	 * @param string $password
	 * @return mixed User instance or null if user is not found
	 */
	public static function getInstanceByLogin($email, $password)
	{
		$loginCond = new EqualsCond(new ARFieldHandle('User', 'email'), $email);
		//$loginCond->addAND(new EqualsCond(new ARFieldHandle('User', 'password'), md5($password)));
		$loginCond->addAND(new EqualsCond(new ARFieldHandle('User', 'isEnabled'), true));

		$recordSet = ActiveRecordModel::getRecordSet(__CLASS__, new ARSelectFilter($loginCond));

		if (!$recordSet->size())
		{
			return null;
		}
		else
		{
			$user = $recordSet->get(0);
			return $user->isPasswordValid($password) ? $user : null;
		}
	}

	/**
	 * Gets an instance of user by using user's e-mail
	 *
	 * @param string $email
	 * @return mixed User instance or null if user is not found
	 */
	public static function getInstanceByEmail($email)
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'email'), $email));
		$recordSet = ActiveRecordModel::getRecordSet(__CLASS__, $filter);

		if (!$recordSet->size())
		{
			return null;
		}
		else
		{
			return $recordSet->get(0);
		}
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function isAnonymous()
	{
		return $this->getID() == self::ANONYMOUS_USER_ID;
	}

	/**
	 * Generate a random password
	 *
	 * @return string
	 */
	public function getAutoGeneratedPassword($length = 8)
	{
		$chars = array();
		for ($k = 1; $k <= $length; $k++)
		{
			$chars[] = chr(rand(97, 122));
		}

		return implode('', $chars);
	}

	/**
	 * Change user password
	 *
	 * @param string $password New password
	 */
	public function setPassword($password)
	{
		$salt = $this->getAutoGeneratedPassword(16);
		$saltedPassword = md5($password . $salt);
		$this->password->set($saltedPassword . ':' . $salt);
		$this->newPassword = $password;
	}

	public function isPasswordValid($password)
	{
		$password = trim($password);
		$parts = explode(':', $this->password->get());
		$hash = array_shift($parts);
		$salt = array_shift($parts);

		return md5($password . $salt) == $hash;
	}

	/**
	 * Checks if a user can access a particular controller/action identified by a role string (handle)
	 *
	 * Role string represents hierarchial role, that grants access to a given node:
	 * rootNode.someNode.lastNode
	 *
	 * (i.e. admin.store.catalog) this role string identifies that user has access to
	 * all actions/controller that are mapped to this string (admin.store.catalog.*)
	 *
	 * @param string $roleName
	 * @return bool
	 */
	public function hasAccess($roleName)
	{
		if ($this->hasBackendAccess || !empty($this->grantedRoles[$roleName]))
		{
			return true;
		}

		// no role provided
		if (!$roleName)
		{
			return true;
		}

		if (!$this->getID())
		{
			return false;
		}

		if ('login' == $roleName)
		{
			return $this->getID() > 0;
		}
		else if ('backend' == $roleName)
		{
			return $this->hasBackendAccess();
		}

		if ($this->isAnonymous())
		{
			return false;
		}
		else
		{
			$this->load(array('UserGroup'));

			if (!$this->userGroup->get())
			{
				return false;
			}

			return $this->userGroup->get()->hasAccess($roleName);
		}
	}

	public function allowBackendAccess()
	{
		$this->hasBackendAccess = true;
	}

	/**
	 *	Dynamically grant access to a role
	 */
	public function grantAccess($roleName)
	{
		$this->grantedRoles[$roleName] = true;
	}

	/**
	 * Determine if the user is allowed to access the admin backend (has at least one permission)
	 *
	 * @return boolean
	 */
	public function hasBackendAccess()
	{
		if ($this->hasBackendAccess)
		{
			return true;
		}

		if ($this->isAnonymous())
		{
			return false;
		}
		else
		{
			$this->load(array('UserGroup'));
			if (!$this->userGroup->get())
			{
				return false;
			}
			else
			{
				$this->userGroup->get()->load();
			}

			$this->userGroup->get()->loadRoles();

			return count($this->userGroup->get()->getAppliedRoles()) > 0;
		}
	}

	/**
	 * Check's if this user is loged in. This function will return true only if this
	 * user is loged within this particular session.
	 *
	 * In short that means that this function will return true only if you are this
	 * user and you are currently loged in
	 *
	 * @return boolean
	 */
	public function isLoggedIn()
	{
		return ($this->getID() != self::ANONYMOUS_USER_ID);
	}

	public function setPreference($key, $value)
	{
		$preferences = $this->preferences->get();
		$preferences[$key] = $value;
		$this->preferences->set($preferences, true);
	}

	public function getPreference($key)
	{
		$preferences =& $this->preferences->get();
		if (isset($preferences[$key]))
		{
			return $preferences[$key];
		}

		return null;
	}

	/**
	 * Get user full name inlcuding both first and last names
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->firstName->get() . ' ' . $this->lastName->get();
	}

	public function invalidateSessionCache()
	{
		if ($this->isAnonymous())
		{
			return;
		}

		$f = new ARUpdateFilter(eq(f('SessionData.userID'), $this->getID()));
		$f->addModifier('cacheUpdated', 0);
		self::updateRecordSet('SessionData', $f);
	}

	/*####################  Saving ####################*/

	public function loadRequestData(Request $request)
	{
		if (!$request->get('password'))
		{
			$request->remove('password');
		}

		return parent::loadRequestData($request);
	}

	/**
	 * Save user in the database
	 */
	public function save($forceOperation = null)
	{
		// auto-generate password if not set
		if (!$this->password->get())
		{
			$this->setPassword($this->getAutoGeneratedPassword());
		}

		return parent::save($forceOperation);
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$array = parent::toArray();
		$array['newPassword'] = $this->newPassword;

		$this->setArrayData($array);

		return $array;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);
		$array['fullName'] = $array['firstName'] . ' ' . $array['lastName'];

		return $array;
	}

	/*####################  Get related objects ####################*/

	/**
	 * Load user address
	 */
	public function loadAddresses()
	{
		$this->load();

		if ($this->defaultBillingAddress->get())
		{
			$this->defaultBillingAddress->get()->load(array('UserAddress'));
		}

		if ($this->defaultShippingAddress->get())
		{
			$this->defaultShippingAddress->get()->load(array('UserAddress'));
		}
	}

	public function getOrder($id)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $id));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));

		$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);
		if ($s->size())
		{
			$order = $s->get(0);
			$order->loadAll();
			return $order;
		}
	}

	public function getBillingAddressArray($defaultFirst = true)
	{
		if (!$this->isAnonymous())
		{
			return ActiveRecordModel::getRecordSetArray('BillingAddress', $this->getBillingAddressFilter($defaultFirst), array('UserAddress'));
		}
		else if ($this->defaultBillingAddress->get())
		{
			return array($this->defaultBillingAddress->get()->toArray());
		}
	}

	public function getBillingAddressSet($defaultFirst = true)
	{
		return ActiveRecordModel::getRecordSet('BillingAddress', $this->getBillingAddressFilter($defaultFirst), array('UserAddress'));
	}

	public function getShippingAddressArray($defaultFirst = true)
	{
		if (!$this->isAnonymous())
		{
			return ActiveRecordModel::getRecordSetArray('ShippingAddress', $this->getShippingAddressFilter($defaultFirst), array('UserAddress'));
		}
		else if ($this->defaultShippingAddress->get())
		{
			return array($this->defaultShippingAddress->get()->toArray());
		}
	}

	public function getShippingAddressSet($defaultFirst = true)
	{
		return ActiveRecordModel::getRecordSet('ShippingAddress', $this->getShippingAddressFilter($defaultFirst), array('UserAddress'));
	}

	private function getShippingAddressFilter($defaultFirst = true)
	{
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle('ShippingAddress', 'userID'), $this->getID()));
		if (!$defaultFirst)
		{
			$f->setOrder(new ARExpressionHandle('ID = ' . $this->defaultShippingAddress->get()->getID()));
		}

		return $f;
	}

	private function getBillingAddressFilter($defaultFirst = true)
	{
		$f = new ARSelectFilter();
		$f->setCondition(new EqualsCond(new ARFieldHandle('BillingAddress', 'userID'), $this->getID()));
		if (!$defaultFirst)
		{
			$f->setOrder(new ARExpressionHandle('ID = ' . $this->defaultBillingAddress->get()->getID()));
		}

		return $f;
	}

	public function serialize($skippedRelations = array(), $properties = array())
	{
		$properties[] = 'specificationInstance';
		return parent::serialize($skippedRelations, $properties);
	}

	public function __destruct()
	{
		return parent::destruct(array('defaultShippingAddressID', 'defaultBillingAddressID'));
	}
}

?>
