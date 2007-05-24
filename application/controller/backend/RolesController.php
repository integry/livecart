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
 * @role userGroup
 */
class RolesController extends StoreManagementController
{
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
                $rc = count($roles) - 1;
                if(isset($roles[$rc]) && $roles[$rc]['parent'] === 0)
                {
                    $parentID = 'smart-' . $roles[$rc]['ID'];
                    
                    $roles[] = array(
                        'ID' => $roles[$rc]['ID'],
                        'name' => $roles[$rc]['name'] . '.misc',
                        'translation' => $this->translate('_role_' . strtolower($roles[$rc]['name']) . '_misc'),
                        'parent' => $parentID,
                        'indent' => 1
                    );
                    
                    $roles[$rc]['ID'] = $parentID;
                }
                $roleArray['parent'] = $parentID;
            }
            else
            {
                $parentID = $roleArray['ID'];
                $roleArray['parent'] = 0;
            }
            
            $roleArray['translation'] = $this->translate(strtolower("_role_" . str_replace('.', '_', $roleArray['name'])));
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
	
	/**
	 * Saves changes to current group roles
	 * 
	 * @role update
	 */
    public function update()
    {
	    $userGroupID = (int)$this->request->getValue('id');
        $userGroup = UserGroup::getInstanceByID($userGroupID);
        
        $validator = $this->createRolesFormValidator($userGroup);
        if($validator->isValid())
        {
            foreach(explode(',', $this->request->getValue('checked')) as $roleID)
            {
                if(preg_match('/smart/', $roleID)) continue;
                $userGroup->applyRole(Role::getInstanceByID((int)$roleID));
            }
            
            foreach(explode(',', $this->request->getValue('unchecked')) as $roleID)
            {
                if(preg_match('/smart/', $roleID)) continue;
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

}
?>