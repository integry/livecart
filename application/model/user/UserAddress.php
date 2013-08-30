<?php


/**
 * Customer billing or shipping address
 *
 * @package application/model/user
 * @author Integry Systems <http://integry.com>
 */
class UserAddress extends ActiveRecordModel implements EavAble
{
	/**
	 * Define database schema
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $stateID", "State", "ID", 'State;
		public $eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);
		public $firstName;
		public $lastName;
		public $companyName;
		public $address1;
		public $address2;
		public $city;
		public $stateName;
		public $postalCode;
		public $countryID;
		public $phone;
		$schema->registerAutoReference('stateID');
	}

	public static function getNewInstance()
	{
		return new self();
	}

	public static function getNewInstanceByTransaction(TransactionDetails $details)
	{
		$instance = self::getNewInstance();
		$instance->firstName = $details->firstName->get());
		$instance->lastName = $details->lastName->get());
		$instance->companyName = $details->companyName->get());
		$instance->address1 = $details->address->get());
		$instance->city = $details->city->get());
		$instance->stateName = $details->state->get());
		$instance->postalCode = $details->postalCode->get());
		$instance->countryID = $details->country->get());
		$instance->phone = $details->phone->get());
		return $instance;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['countryName'] = self::getApplication()->getLocale()->info()->getCountryName($array['countryID']);
		$array['fullName'] = $array['firstName'] . ' ' . $array['lastName'];
		if (!empty($array['State']) && is_array($array['State']))
		{
			$array['stateName'] = $array['State']['name'];
		}

		$array['compact'] = self::getAddressString($array, ', ');
		$array['compactAddressOnly'] = self::getAddressString(array_diff_key($array, array_flip(array('fullName', 'firstName', 'lastName'))), ', ');

		if (!isset($array['stateID']) && isset($array['State']) && is_array($array['State']) && array_key_exists('ID', $array['State']))
		{
			$array['stateID'] = $array['State']['ID'];
		}

		return $array;
	}

	public function toString($separator = "\n")
	{
		return self::getAddressString($this->toArray(), $separator);
	}

	public function getFullName()
	{
		return $this->firstName->get() . ' ' . $this->lastName->get();
	}

	private static function getAddressString(array $addressArray, $separator)
	{
		$address = array();

		if (isset($addressArray['firstName']))
		{
			$address[] = implode(' ', array($addressArray['firstName'], $addressArray['lastName']));
		}

		foreach (array('companyName', 'address1', 'address2') as $field)
		{
			$address[] = $addressArray[$field];
		}

		$address[] = implode(', ', array_filter(array($addressArray['city'], $addressArray['postalCode']), 'trim'));
		$address[] = $addressArray['stateName'];

		if ($addressArray['countryID'])
		{
			$address[] =  self::getApplication()->getLocale()->info()->getCountryName($addressArray['countryID']);
		}

		return implode($separator, array_filter($address, 'trim'));
	}

	public function loadRequestData(\Phalcon\Http\Request $request, $prefix = '')
	{
		parent::loadRequestData($request, $prefix);

		if ($request->get($prefix . 'stateID'))
		{
			$this->state = State::getInstanceByID((int)$request->get($prefix . 'stateID'), true));
			$this->stateName = null);
		}
		else if ($request->isValueSet($prefix . 'stateName'))
		{
			$this->stateName = $request->get($prefix . 'stateName'));
			$this->state = null);
		}
	}

	public function serialize($skippedRelations = array(), $properties = array())
	{
		$properties[] = 'specificationInstance';
		$properties[] = 'serializedState';

		// for some reason directly unserializing State class causes a segfault...
		if ($this->state->get())
		{
			$this->serializedState = $this->state->get()->getID();
			$this->state->setNull();
		}

		return parent::serialize($skippedRelations, $properties);
	}

	public function unserialize($serialized)
	{
		parent::unserialize($serialized);

		if ($this->serializedState)
		{
			$this->state = State::getInstanceByID($this->serializedState, true));
		}
	}

	public function __destruct()
	{
		parent::destruct(array('stateID'));
	}
}
?>
