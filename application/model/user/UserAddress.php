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
	
	public static function transformArray($array, ARSchema $schema)
	{          
        $array['countryName'] = self::getApplication()->getLocale()->info()->getCountryName($array['countryID']);
        $array['fullName'] = $array['firstName'] . ' ' . $array['lastName'];
        return $array;   
    }	
    
    public function toString()
    {
        $addressString = '';
        
        // Name
        if($this->firstName->get() != '') $addressString .= $this->firstName->get();
        if($this->firstName->get() != '' && $this->lastName->get() != '') $addressString .= " ";
        if($this->lastName->get() != '') $addressString .= $this->lastName->get();
        if($this->firstName->get() != '' || $this->lastName->get() != '') $addressString .= "\n";
        
        // Company name
        if($this->companyName->get() != '') $addressString .= $this->companyName->get() . "\n";
        
        // Address
        if($this->address1->get() != '') $addressString .= $this->address1->get() . "\n";
        if($this->address2->get() != '') $addressString .= $this->address2->get() . "\n";
        
        // City and postal code
        if($this->city->get() != '') $addressString .= $this->city->get();
        if($this->city->get() != '' && $this->postalCode->get() != '') $addressString .= ", ";
        if($this->postalCode->get() != '') $addressString .= $this->postalCode->get();
        if($this->city->get() != '' || $this->postalCode->get() != '') $addressString .= "\n";
        
        if($this->countryID->get() != '') $addressString .=  $this->getApplication()->getLocale()->info()->getCountryName($this->countryID->get()) . "\n";

        return $addressString;
    }
}
?>