<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.role.*");

/**
 * All users can be assigned to a group. Assigning users to a group is necessary to provide admin
 * privileges for a user. User access privileges can be modified and fine-grained at the user group level.
 *
 * @package application.model.role
 * @author Integry Systems <http://integry.com> 
 */
class UserGroup extends ActiveRecordModel 
{
    private $appliedRoles = array();
    private $canceledRoles = array();
    private $rolesLoaded = false;
        
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);

		$schema->setName("UserGroup");
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARVarchar::instance(60)));
		$schema->registerField(new ARField("description", ARVarchar::instance(100)));
	}
	
	/*####################  Static method implementations ####################*/	
	
	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return TaxRate
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new user group
	 * 
	 * @param DeliveryZone $deliveryZone Delivery zone instance
	 * @param Tax $tax Tax type
	 * @param float $rate Rate in percents
	 * @return TaxRate
	 */
	public static function getNewInstance($name, $description = '')
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);

	  	$instance->name->set($name);
	  	$instance->description->set($description);
	  	
	  	return $instance;
	}

	/**
	 * Load service rates record set
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
	
    /*####################  Value retrieval and manipulation ####################*/		
	
	public function loadRoles($force = false)
	{
	    if(!$this->rolesLoaded || $force)
	    {
	        $associations = AccessControlAssociation::getRecordSetArrayByUserGroup($this, new ARSelectFilter(), self::LOAD_REFERENCES);
		    foreach($associations as $assoc)
		    {
		        $this->appliedRoles[$assoc['roleID']] = $assoc['Role'];
		    }
		    
		    $this->rolesLoaded = true;
	    }
	}
	
	public function setAllRoles()
	{
		// add Roles to database
        Role::cleanUp();
        
        foreach (Role::getRecordSet(new ARSelectFilter()) as $role)
        {
            $this->applyRole($role);
        }
	}
	
	/**
	 * Array(string) of applied roles
	 * 
	 * @param array|string
	 */
	public function hasAccess($actionRoleNames)
	{
	    if(empty($actionRoleNames)) return true;
	    if(!is_array($actionRoleNames)) $actionRoleNames = array($actionRoleNames);
	    
	    $this->loadRoles();
	    $appliedRoleNames = array();
	    foreach($this->getAppliedRoles() as $role)
	    {
	        $appliedRoleNames[] = $role['name'];
        }
        
	    return count(array_intersect($actionRoleNames, $appliedRoleNames)) > 0;
	}

	public function applyRole(Role $role)
	{
	    if(!$role->isExistingRecord()) return;
	    
	    $this->appliedRoles[$role->getID()] = $role;
	    
	    if(isset($this->canceledRoles[$role->getID()]))
	    {
	        unset($this->canceledRoles[$role->getID()]);
	    }
	}
	
	public function cancelRole(Role $role)
	{
	    if(!$role->isExistingRecord()) return;
	    
	    $this->canceledRoles[$role->getID()] = $role;
	    
	    if(isset($this->appliedRoles[$role->getID()]))
	    {
	        unset($this->appliedRoles[$role->getID()]);
	    }
	}
	
	public function save($forceOperation = 0)
	{
	    parent::save($forceOperation);
	    
	    $this->updateRoles();
	}
	
	private function updateRoles()
	{	   
   	    if(count($this->canceledRoles) > 0)
	    {
		    // Delete canceled associations
		    $deleteFilter = new ARDeleteFilter();
		    
		    $condition = new EqualsCond(new ARFieldHandle('AccessControlAssociation', "userGroupID"), $this->getID());
		    
	        $roleConditions = new EqualsCond(new ARFieldHandle('AccessControlAssociation', "roleID"), reset($this->canceledRoles)->getID());
		    foreach($this->canceledRoles as $key => $role)
		    {
		        if($role->isExistingRecord())
		        {
		            $roleConditions->addOR(new EqualsCond(new ARFieldHandle('AccessControlAssociation', "roleID"), $role->getID()));
		        }
		        else
		        {
		            unset($this->canceledRoles[$key]);
		        }
		    }
		    
		    $condition->addAND($roleConditions);
		    $deleteFilter->setCondition($condition);
		    
		    if(!empty($this->canceledRoles))
		    {
		        AccessControlAssociation::deleteRecordSet('AccessControlAssociation', $deleteFilter);
		    }
	    }
	    
		    
	    if(count($this->appliedRoles) > 0)
	    {
		    // adding new associations is a bit trickier
			// First, find all nodes that are already in DB
		    // There is no point to apply them 
		    $appliedRolesFilter = new ARSelectFilter();
		    $appliedIDs = array();
	        $condition = new EqualsCond(new ARFieldHandle('AccessControlAssociation', "userGroupID"), $this->getID());
	        
	        $roleConditions = new EqualsCond(new ARFieldHandle('AccessControlAssociation', "roleID"), reset($this->appliedRoles)->getID());
		    foreach($this->appliedRoles as $key => $role)
		    {
	   	        if($role->isExistingRecord())
		        {
		            $roleConditions->addOR(new EqualsCond(new ARFieldHandle('AccessControlAssociation', "roleID"), $role->getID()));
		        }
		        else
		        {
		            unset($this->appliedRoles[$key]);
		        }
		    }
		    
		    $condition->addAND($roleConditions);
	        $appliedRolesFilter->setCondition($condition);
		    
		    // Unset already applied nodes
		    foreach(AccessControlAssociation::getRecordSetByUserGroup($this, $appliedRolesFilter, self::LOAD_REFERENCES) as $assoc)
		    {
		        unset($this->appliedRoles[$assoc->role->get()->getID()]);
		    }
		    
		    // Apply roles
		    foreach($this->appliedRoles as $role)
		    {
		        $assoc = AccessControlAssociation::getNewInstance($this, $role);
		        $assoc->save();
		    }
	    }
	}
	
	public function getAppliedRoles()
	{
	    return $this->appliedRoles;
	}
	
	/*####################  Get related objects ####################*/        	
	
	/**
	 * Load users in this group
	 *
	 * @param DeliveryZone $deliveryZone 
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public function getUsersRecordSet(ARSelectFilter $filter = null, $loadReferencedRecords = array('UserGroup'))
	{
		return User::getRecordSetByGroup($this, $filter, $loadReferencedRecords);
	}

	public function getRolesRecordSet(ARSelectFilter $filter = null, $loadReferencedRecords = false)
	{
	    if(!$filter) 
        {
            $filter = new ARSelectFilter();
        }
        
        $rolesRecordSet = new ARSet();
        
        foreach(AccessControlAssociation::getRecordSetByUserGroup($this, $filter) as $association)
        {
            $rolesRecordSet->add($association->role->get());
        }
        
        return $rolesRecordSet;
	}	
}

?>