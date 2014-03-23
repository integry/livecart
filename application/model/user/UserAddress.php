<?php

namespace user;

/**
 * Customer billing or shipping address
 *
 * @package application/model/user
 * @author Integry Systems <http://integry.com>
 */
class UserAddress extends \ActiveRecordModel implements \eav\EavAble
{
	public $ID;
//	public $stateID", "State", "ID", 'State;
	public $eavObject;
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

	public function initialize()
	{
		$cascade = array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            );

		$this->hasOne('eavObjectID', 'eav\EavObject', 'ID', array('foreignKey' => $cascade, 'alias' => 'EavObject'));
	}

	public static function getNewInstance()
	{
		return new self();
	}

	public static function getNewInstanceByTransaction(TransactionDetails $details)
	{
		$instance = self::getNewInstance();
		$instance->firstName = $details->firstName;
		$instance->lastName = $details->lastName;
		$instance->companyName = $details->companyName;
		$instance->address1 = $details->address;
		$instance->city = $details->city;
		$instance->stateName = $details->state;
		$instance->postalCode = $details->postalCode;
		$instance->countryID = $details->country;
		$instance->phone = $details->phone;
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
		return $this->firstName . ' ' . $this->lastName;
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
			$this->state = State::getInstanceByID((int)$request->get($prefix . 'stateID'), true);
			$this->stateName = null;
		}
		else if ($request->has($prefix . 'stateName'))
		{
			$this->stateName = $request->get($prefix . 'stateName');
			$this->state = null;
		}
	}

	public function serialize($skippedRelations = array(), $properties = array())
	{
		$properties[] = 'specificationInstance';
		$properties[] = 'serializedState';

		// for some reason directly unserializing State class causes a segfault...
		if ($this->state)
		{
			$this->serializedState = $this->state->getID();
			$this->state = null;
		}

		return parent::serialize($skippedRelations, $properties);
	}

	public function unserialize($serialized)
	{
		parent::unserialize($serialized);

		if ($this->serializedState)
		{
			$this->state = State::getInstanceByID($this->serializedState, true);
		}
	}
}
?>
