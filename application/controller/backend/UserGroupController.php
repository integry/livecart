<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * @package application.controller.backend
 * @role userGroup
 */
class UserGroupController extends StoreManagementController
{
	/**
	 * Action shows filters and datagrid.
	 * @return ActionResponse
	 */
	public function index()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group) 
		{
		    $userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}
		    
		$response = new ActionResponse();
		$response->set('userGroups', $userGroups);
		return $response;
	}
    
    public function edit()
    {
	    $group = UserGroup::getInstanceByID((int)$this->request->get('id'), true);
	    $form = $this->createUserGroupForm($group);

		$response = new ActionResponse();
		$response->set('userGroup', $group->toArray());
	    $response->set('userGroupForm', $form);
	    
	    return $response;
    }
    
	public function changeColumns()
	{		
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.userGroup', 'users', array('id' => $this->request->get('group')));
	}

	public function lists()
	{
	    $id = (int)substr($this->request->get('id'), 6);
	    if($id > 0)
	    {
	        $showAllGroups = false;
	        $userGroup = UserGroup::getInstanceByID($id, ActiveRecord::LOAD_DATA);
	    }
	    else if($id == -1)
	    {
	        $showAllGroups = false;
	        $userGroup = null;
	    }
	    else if($id == -2)
	    {
	        $showAllGroups = true;
	        $userGroup = null;
	    }
	    else
	    {
	        return;
	    }

	    $filter = new ARSelectFilter();
	    new ActiveGrid($this->application, $filter);
	    if($showAllGroups)
	    {
	        $usersArray = User::getRecordSet($filter, array('UserGroup'))->toArray();
	    }
	    else
	    {
	        $usersArray = User::getRecordSetByGroup($userGroup, $filter, array('UserGroup'))->toArray();
	    }
	    
		$displayedColumns = $this->getDisplayedColumns($userGroup);

    	$data = array();
		foreach ($usersArray as $user)
    	{
            $record = array();
            foreach ($displayedColumns as $column => $type)
            {
                list($class, $field) = explode('.', $column, 2);
                
                if ('User' == $class)
                {
					$value = isset($user[$field]) ? $user[$field] : '';
                }
				
                if ('UserGroup' == $class)
                {
					$value = isset($user['UserGroup'][$field]) ? $user['UserGroup'][$field] : '';
                }
				
				if ('bool' == $type)
				{
					$value = $value ? $this->translate('_yes') : $this->translate('_no');
				}
				
				$record[] = $value;
            }
            
            $data[] = $record;
        }
    	
    	$return = array();
    	$return['columns'] = array_keys($displayedColumns);
    	$return['totalCount'] = count($usersArray);
    	$return['data'] = $data;
    	
    	return new JSONResponse($return);	  	  	
	}
	
	public function users()
	{
	    $id = (int)$this->request->get("id");
	    if($id > 0)
	    {
	        $showAllGroups = false;
	        $userGroup = UserGroup::getInstanceByID($id, ActiveRecord::LOAD_DATA);
	    }
	    else if($id == -1)
	    {
	        $showAllGroups = false;
	        $userGroup = null;
	    }
	    else if($id == -2)
	    {
	        $showAllGroups = true;
	        $userGroup = null;
	    }
	    else
	    {
	        return;
	    }
	        
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = $this->getDisplayedColumns();
		
		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);		
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);
			
		$response = new ActionResponse();
		
		$availableUserGroups = array('' => '');
        foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
        {
            $availableUserGroups[$group->getID()] = $group->name->get();
        }
          
        $userArray = array('UserGroup' => $id, 'ID' => 0);
        $form = UserController::createUserForm($this, null);
        $form->setData($userArray);
        
	    $response->set('user', $userArray);
	    $response->set('availableUserGroups', $availableUserGroups);
	    $response->set('form', $form);
	    
        $response->set("massForm", $this->getMassForm());
        $response->set("displayedColumns", $displayedColumns);
        $response->set("availableColumns", $availableColumns);
		$response->set("userGroupID", $id);
		$response->set("offset", $this->request->get('offset'));
		$response->set("totalCount", '0');
				
		return $response;
	}

	/**
	 * @role update
	 */
    public function save()
    {
        $name = $this->request->get('name');
        $description = $this->request->get('description');
        
        if($id = (int)$this->request->get('id'))
        {
            $group = UserGroup::getInstanceByID($id);
        }
        else
        {
            $group = UserGroup::getNewInstance($name, $description);
        }
        
        $validator = $this->createUserGroupFormValidator($group);
        if($validator->isValid())
        {            
            $group->name->set($name);
            $group->description->set($description);
            
	        $group->save();
	        
	        return new JSONResponse(array('group' => $group->toArray()), 'success', $this->translate('_user_group_successfully_saved'));
        }
        else
        {
            return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_group'));
        }
    }

	/**
	 * @role create
	 */
	public function create()
	{
	    $userGroup = UserGroup::getNewInstance($this->translate('_new_user_group'));
	    $userGroup->save();
	    
		return new JSONResponse($userGroup->toArray(), 'success', $this->translate('_new_user_group_successfully_created'));
	}
	
	/**
	 * @role remove
	 */
	public function remove()
	{
		$userGroup = UserGroup::getInstanceByID((int)$this->request->get("id"), true);
		$userGroupArray = $userGroup->toArray();
		$userGroup->delete();
		
		return new JSONResponse(array('userGroup' => $userGroupArray), 'success', $this->translate('_user_group_was_successfully_removed'));
	}

	/**
	 * @return Form
	 */
	private function createUserGroupForm(UserGroup $group)
	{
	    $form = new Form($this->createUserGroupFormValidator($group)); 
        $form->setData($group->toArray());
	    
	    return $form;
	}
	
	/**
	 * @return RequestValidator
	 */
	private function createUserGroupFormValidator(UserGroup $group)
	{
		$validator = new RequestValidator("userGroupForm_" . $group->isExistingRecord() ? $group->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_name_should_not_be_empty")));
		
		return $validator;
	}
		
	protected function getDisplayedColumns()
	{	
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');		

		if (!$displayedColumns)
		{
			$displayedColumns = array(
			 	'User.email',
				'UserGroup.name',
				'User.firstName', 
				'User.lastName', 
				'User.companyName', 
				'User.dateCreated', 
				'User.isEnabled'
			);				
		}
		
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		// User ID is always passed as the first column
		$displayedColumns = array_merge(array('User.ID' => 'numeric'), $displayedColumns);
				
		// set field type as value
		foreach ($displayedColumns as $column => $foo)
		{
			if (is_numeric($displayedColumns[$column]))
			{
				$displayedColumns[$column] = $availableColumns[$column]['type'];					
			}
		}

		return $displayedColumns;		
	}
	
	protected function getAvailableColumns()
	{
		// get available columns
		$availableColumns = array();
		foreach (ActiveRecordModel::getSchemaInstance('User')->getFieldList() as $field)
		{
			$type = ActiveGrid::getFieldType($field);
			
			if ($field->getName() == 'password' || !$type)
			{
			    continue;
			}		
            
			$availableColumns['User.' . $field->getName()] = $type;
		}		
		
		$availableColumns['UserGroup.name'] = 'text';

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array(
				'name' => $this->translate($column), 
				'type' => $type
			);	
		}


		return $availableColumns;
	}
	
    protected function getMassForm()
    {
		$validator = new RequestValidator("UsersFilterFormValidator", $this->request);		
		
        return new Form($validator);                
    }
	

    


    
}
?>