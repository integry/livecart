<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");

/**
 *
 * @package application.controller.backend
 * @role user
 */
class UserController extends StoreManagementController
{
	/**
	 * Action shows filters and datagrid.
	 * @return ActionResponse
	 */
	public function index()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => 'root', 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group) 
		{
		    $userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}
		    
		$response = new ActionResponse();
		$response->setValue('userGroups', $userGroups);
		return $response;
	    
	}
	
	public function users()
	{
	    $id = (int)$this->request->getValue("id");
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
        $form = $this->createUserForm(null);
        $form->setData($userArray);
        
	    $response->setValue('user', $userArray);
	    $response->setValue('availableUserGroups', $availableUserGroups);
	    $response->setValue('form', $form);
	    
        $response->setValue("massForm", $this->getMassForm());
        $response->setValue("displayedColumns", $displayedColumns);
        $response->setValue("availableColumns", $availableColumns);
		$response->setValue("userGroupID", $id);
		$response->setValue("offset", $this->request->getValue('offset'));
		$response->setValue("totalCount", '0');
				
		return $response;
	}	
	
	public function changeColumns()
	{		
		$columns = array_keys($this->request->getValue('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.user', 'users', array('id' => $this->request->getValue('group')));
	}

	public function lists()
	{
	    $id = (int)substr($this->request->getValue('id'), 6);
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
	    new ActiveGrid($this->request, $filter);
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
	
	protected function getDisplayedColumns()
	{	
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');		

		if (!$displayedColumns)
		{
			$displayedColumns = array(
				'User.ID', 
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
			$fieldType = $field->getDataType();
			
			if($field->getName() == 'password')
			{
			    continue;
			}
			if ($field instanceof ARForeignKeyField)
			{
			  	continue;
			}		            
			if ($field instanceof ARPrimaryKeyField)
			{
			  	continue;
			}		            
			elseif ($fieldType instanceof ARBool)
			{
			  	$type = 'bool';
			}	  
			elseif ($fieldType instanceof ARNumeric)
			{
				$type = 'numeric';	  	
			}			
			else
			{
			  	$type = 'text';
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
    
    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
		$filters = (array)json_decode($this->request->getValue('filters'));
		$this->request->setValue('filters', $filters);
		
        $grid = new ActiveGrid($this->request, $filter, 'User');
        $filter->setLimit(0);
        					
		$users = ActiveRecordModel::getRecordSet('User', $filter, User::LOAD_REFERENCES);
		
        $act = $this->request->getValue('act');
		$field = array_pop(explode('_', $act, 2));           

        foreach ($users as $user)
		{
            if (substr($act, 0, 7) == 'enable_')
            {
                $user->setFieldValue($field, 1);    
            }        
            else if (substr($act, 0, 8) == 'disable_')
            {
                $user->setFieldValue($field, 0);                 
            } 
            else if ('delete' == $act)
            {
				$user->delete();
			}         
            
			$user->save();
        }		
		
		return new JSONResponse($this->request->getValue('act'));	
    } 
    
	public function info()
	{
	    $user = User::getInstanceById((int)$this->request->getValue('id'), ActiveRecord::LOAD_DATA, array('UserGroup'));
		
        $availableUserGroups = array('' => '');
        foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
        {
            $availableUserGroups[$group->getID()] = $group->name->get();
        }
        
	    $response = new ActionResponse();	    
	    $response->setValue('user', $user->toFlatArray());
	    $response->setValue('availableUserGroups', $availableUserGroups);
	    $response->setValue('form', $this->createUserForm($user));
		
		return $response;
	}
	
	/**
	 * @return RequestValidator
	 */
    private function createUserFormValidator()
    {
		$validator = new RequestValidator("UserForm", $this->request);		            
		
		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_email_empty')));		
		$validator->addCheck('email', new IsValidEmailCheck($this->translate('_err_invalid_email')));  
		$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_err_first_name_empty')));		
		$validator->addCheck('lastName', new IsNotEmptyCheck($this->translate('_err_last_name_empty')));
		$validator->addCheck('password1', new PasswordEqualityCheck(
						                        $this->translate('_err_passwords_are_not_the_same'), 
						                        $this->request->getValue('password2'), 
												'password2'
					                        ));
					                        
		$validator->addCheck('password2', new PasswordEqualityCheck(
		                                        $this->translate('_err_passwords_are_not_the_same'), 
		                                        $this->request->getValue('password1'), 
												'password1'
	                                        ));

		$validator->addCheck('userGroupID', new IsNumericCheck($this->translate('_err_invalid_group')));
		
		return $validator;
    }

    /**
     * @return Form
     * @role user.createUser
     */
	private function createUserForm(User $user = null)
	{
		$form = new Form($this->createUserFormValidator());
		
		if($user)
		{
		    $form->setData($user->toFlatArray());
		}

		return $form;
	}
	
	public function saveInfo()
	{
	  	if ($id = (int)$this->request->getValue('id'))
	  	{
		  	$user = User::getInstanceByID((int)$id);
	  	}
	  	else
	  	{
	  	    $user = null;
	  	}
	    
   		$validator = $this->createUserFormValidator($user);
		if ($validator->isValid())
		{
		    $email = $this->request->getValue('email');
		    $password = $this->request->getValue('password');
		    $firstName = $this->request->getValue('firstName');
		    $lastName = $this->request->getValue('lastName');
		    $companyName = $this->request->getValue('companyName');
		    		    
		    if(User::getInstanceByEmail($email))
		    {
		        return new JSONResponse(array('status' => 'failure', 'errors' => array('email' => $this->translate('_err_this_email_is_already_being_used_by_other_user'))));
		    }
		    
		    if($groupID = (int)$this->request->getValue('UserGroup'))
		    {
		        $group = UserGroup::getInstanceByID((int)$groupID);
		    }
		    else
		    {
		        $group = null;
		    }
		
		  	if (!$user)
			{
			    $user = User::getNewInstance($email, $password, $group);
			}
			
			$user->lastName->set($lastName);
			$user->firstName->set($firstName);
			$user->setPassword($password);
			$user->companyName->set($companyName);
			$user->email->set($email);
			$user->userGroup->set($group);
			
			$user->save();
			
			return new JSONResponse(array('status' => 'success', 'user' => $user->toArray()));
		}
		else
		{
		    return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
		}
	}
   
}
?>