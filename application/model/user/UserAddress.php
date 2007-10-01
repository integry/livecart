<?php

ClassLoader::import("application.model.delivery.State");

/**
 * Customer billing or shipping address
 *
 * @package application.model.user
 * @author Integry Systems <http://integry.com>
 */
class UserAddress extends ActiveRecordModel
{
    /**
     * Define database schema
     */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("stateID", "state", "ID", 'State', ARInteger::instance()));
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
		$instance->city->set($details->city->get());
		$instance->stateName->set($details->state->get());
		$instance->postalCode->set($details->postalCode->get());
		$instance->countryID->set($details->country->get());
		$instance->phone->set($details->phone->get());
		return $instance;
	}

	public static function transformArray($array, ARSchema $schema)
	{
        $array['countryName'] = self::getApplication()->getLocale()->info()->getCountryName($array['countryID']);
        $array['fullName'] = $array['firstName'] . ' ' . $array['lastName'];
        if (isset($array['State']))
        {
            $array['stateName'] = $array['State']['name'];
        }        
        
        return $array;
    }

    public function toString()
    {
        $address = array();

		$address[] = implode(' ', array($this->firstName->get(), $this->lastName->get()));
		foreach (array('companyName', 'address1', 'address2') as $field)
		{
			$address[] = $this->$field->get();
		}

		$address[] = implode(', ', array_reduce(array($this->city->get(), $this->postalCode->get()), array($this, 'filterAddress')));

        if ($this->countryID->get())
        {
        	$address[] =  $this->getApplication()->getLocale()->info()->getCountryName($this->countryID->get());
		}

		return implode("\n", array_reduce($address, array($this, 'filterAddress')));
    }

	private function reduceAddress($item)
	{
		return trim($item);
	}
}
?>