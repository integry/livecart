<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.tax.TaxRate");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");
		
		
/**
 * Application settings management
 *
 * @package application.controller.backend
 *
 * @role role
 */
class RolesController extends StoreManagementController
{
    /**
     * @role index
     */
	public function index() 
	{
        Role::cleanUp();
	    
	    $userGroupID = (int)$this->request->getValue('id');
        $userGroup = UserGroup::getInstanceByID($userGroupID);
        $activeRoles = $userGroup->getRolesRecordSet();
        
        $roles = array();
        $parentID = 0;
        foreach(Role::getRecordSet(new ARSelectFilter()) as $role)
        {
            $roleArray = $role->toArray();
            
            $roleArray['indent'] = strpos($roleArray['name'], '.') ? 1 : 0;
            if($roleArray['indent'] > 0)
            {
                $roleArray['parent'] = $parentID;
            }
            else
            {
                $parentID = $roleArray['ID'];
                $roleArray['parent'] = 0;
            }
            
            $roles[] = $roleArray;
        }
        
        $activeRolesIDs = array();
        foreach($activeRoles as $role)
        {
            $activeRolesIDs[] = $role->getID();
        }
        
        
		$form = $this->createRolesForm($userGroup, $activeRoles);
				
		$response = new ActionResponse();
		$response->setValue('form', $form);
		$response->setValue('roles', $roles);
		$response->setValue('userGroup', $userGroup->toArray());
		$response->setValue('activeRolesIDs', $activeRolesIDs);
		
	    return $response;
	}
	
	private function createRolesForm(UserGroup $userGroup, ARSet $activeRoles)
	{
		$form = new Form($this->createRolesFormValidator($userGroup));
		
		$userGroupID = $userGroup->getID();
		$activeRolesCheckboxes = array();
		foreach($activeRoles as $role)
		{
		    $activeRolesCheckboxes['role_' . $role->getID()] = 1;
		}
		
		$form->setData($activeRolesCheckboxes);
		
		return $form;
	}
	
	private function createRolesFormValidator(UserGroup $userGroup)
	{	
		$validator = new RequestValidator('roles_' . $userGroup->getID(), $this->request);
		return $validator;
	}	
	
	/**
	 * @role save
	 *
	 * @return unknown
	 */
    public function save()
    {
	    $userGroupID = (int)$this->request->getValue('id');
        $userGroup = UserGroup::getInstanceByID($userGroupID);
        
        $validator = $this->createRolesFormValidator($userGroup);
        if($validator->isValid())
        {
            foreach(explode(',', $this->request->getValue('checked')) as $roleID)
            {
                $userGroup->applyRole(Role::getInstanceByID((int)$roleID));
            }
            
            foreach(explode(',', $this->request->getValue('unchecked')) as $roleID)
            {
                $userGroup->cancelRole(Role::getInstanceByID((int)$roleID));
            }
            
            $userGroup->save();
            
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
        }
    }
    
}
?>