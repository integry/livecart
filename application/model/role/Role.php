<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Roles
 *
 * @package application.model.roles
 */
class Role extends ActiveRecordModel 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Role");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance(10)));
		$schema->registerField(new ARField("name", ARText::instance(150)));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param integer $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return Role
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}

	/**
	 * Gets an existing record instance by specifying role name
	 * @param string $name
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return Role
	 */
	public static function getInstanceByName($name)
	{		    
	    $filter = new ARSelectFilter();
	    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, "name"), $name));
	    
	    $arSet = self::getRecordSet($filter);
	    return $arSet->getTotalRecordCount() > 0 ? $arSet->get(0) : null;
	}
	
	/**
	 * Create new role rate
	 * 
	 * @param string $name New role name
	 * @return Role
	 */
	public static function getNewInstance($name)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	$instance->name->set($name);
        
	  	return $instance;
	}

	/**
	 * Load roles record set
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
	
	public static function addNewRolesNames($roleNames)
	{
	    if(!is_array($roleNames) || empty($roleNames)) return;

	    $filter = new ARSelectFilter();
	    
        $condition = new EqualsCond(new ARFieldHandle(__CLASS__, "name"), $roleNames[0]);
        foreach($roleNames as $roleName)
        {
            $condition->addOR(new EqualsCond(new ARFieldHandle(__CLASS__, "name"), $roleName));
        }
        
        $filter->setCondition($condition);
	    
   	    // Find new roles
	    $invertedRoleNames = array_flip($roleNames);
	    foreach(self::getRecordSet($filter) as $role)
	    {
	        if(isset($invertedRoleNames[$role->name->get()]))
	        {
	            unset($invertedRoleNames[$role->name->get()]);
	        }
	    }
	    // Add new roles to database
	    $invertedRoleNames = array_flip($roleNames);
	    foreach($invertedRoleNames as $role => $value)
	    {
	        $newRole = Role::getNewInstance($role);
	        $newRole->save();
	    }
	}
}

?>