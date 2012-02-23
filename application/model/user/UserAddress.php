<?php

ClassLoader::import("application.model.delivery.State");
ClassLoader::import("application.model.eav.EavAble");
ClassLoader::import("application.model.eav.EavObject");

/**
 * Customer billing or shipping address
 *
 * @package application.model.user
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
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("stateID", "State", "ID", 'State', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);
		$schema->registerField(new ARField("firstName", ARVarchar::instance(60)));
		$schema->registerField(new ARField("lastName", ARVarchar::instance(60)));
		$schema->registerField(new ARField("companyName", ARVarchar::instance(60)));
		$schema->registerField(new ARField("address1", ARVarchar::instance(255)));
		$schema->registerField(new ARField("address2", ARVarchar::instance(255)));
		$schema->registerField(new ARField("city", ARVarchar::instance(255)));
		$schema->registerField(new ARField("stateName", ARVarchar::instance(255)));
		$schema->registerField(new ARField("postalCode", ARVarchar::instance(50)));
		$schema->registerField(new ARField("countryID", ARChar::instance(2)));
		$schema->registerField(new ARField("phone", ARVarchar::instance(100)));
		$schema->registerAutoReference('stateID');
	}

	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}

	public static function getNewInstanceByTransaction(TransactionDetails $details)
	{
		$instance = self::getNewInstance();
		$instance->firstName->set($details->firstName->get());
		$instance->lastName->set($details->lastName->get());
		$instance->companyName->set($details->companyName->get());
		$instance->address1->set($details->address->get());
		$instance->setFieldValue('city', $details->city->get());
		$instance->stateName->set($details->state->get());
		$instance->postalCode->set($details->postalCode->get());
		$instance->countryID->set($details->country->get());
		$instance->phone->set($details->phone->get());
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

	public function loadRequestData(Request $request, $prefix = '')
	{
		parent::loadRequestData($request, $prefix);

		if ($request->get($prefix . 'stateID'))
		{
			$this->state->set(State::getInstanceByID((int)$request->get($prefix . 'stateID'), true));
			$this->stateName->set(null);
		}
		else if ($request->isValueSet($prefix . 'stateName'))
		{
			$this->stateName->set($request->get($prefix . 'stateName'));
			$this->state->set(null);
		}
	}

	public function serialize($skippedRelations = array(), $properties = array())
	{
		$properties[] = 'specificationInstance';
		return parent::serialize($skippedRelations, $properties);
	}

	public function __destruct()
	{
		parent::destruct(array('stateID'));
	}
}
?>
