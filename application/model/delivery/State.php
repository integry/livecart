<?php

/**
 * Country states/provinces
 *
 * @package application.model.category
 */
class State extends ActiveRecordModel
{
    /**
     * Define database schema
     */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("countryID", ARChar::instance(2)));
		$schema->registerField(new ARField("code", ARVarchar::instance(40)));
		$schema->registerField(new ARField("name", ARVarchar::instance(100)));
		$schema->registerField(new ARField("subdivisionType", ARVarchar::instance(60)));
	}    
	
	public static function getStatesByCountry($countryCode)
	{
        $f = new ARSelectFilter();
        $f->setCondition(new EqualsCond(new ARFieldHandle('State', 'countryID'), $countryCode));
        $f->setOrder(new ARFieldHandle('State', 'name'));        
        $stateArray = ActiveRecordModel::getRecordSetArray('State', $f);

        $states = array();
        foreach ($stateArray as $state)
        {
            $states[$state['ID']] = $state['name'];
        }     
        
        return $states;       
    }
    
    public static function getAllStates() 
    { 
        return ActiveRecordModel::getRecordSet(__CLASS__, new ARSelectFilter());
    }

	public static function getStateIDByName($countryCode, $name)
	{
        $f = new ARSelectFilter();
        $f->setCondition(new EqualsCond(new ARFieldHandle('State', 'countryID'), $countryCode));
        
        $nameCond = new EqualsCond(new ARFieldHandle('State', 'name'), $name);
        $nameCond->addOr(new EqualsCond(new ARFieldHandle('State', 'code'), $name));
        $f->mergeCondition($nameCond);
        
        $f->setOrder(new ARFieldHandle('State', 'name'));        
        $f->setLimit(1);
        $stateArray = ActiveRecordModel::getRecordSetArray('State', $f);

        if ($stateArray)
        {
            return $stateArray[0]['ID'];
        }
        else
        {
            return 0;
        }

        return $states;       
    }

}
	
?>