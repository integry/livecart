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
     * @role update
     */
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
	    $response->setValue('form', self::createUserForm($this, $user));
		
		return $response;
	}	
	
	/**
	 * @role mass
	 */
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
	
    /**
     * @role update
     */
	public function saveInfo()
	{
	  	if ($id = (int)$this->request->getValue('id'))
	  	{
		  	$user = User::getInstanceByID((int)$id, true);
	  	}
	  	else
	  	{
	  	    $user = null;
	  	}
	    
   		$validator = self::createUserFormValidator($this, $user);
		if ($validator->isValid())
		{
		    $email = $this->request->getValue('email');
		    $password = $this->request->getValue('password1');
		    $firstName = $this->request->getValue('firstName');
		    $lastName = $this->request->getValue('lastName');
		    $companyName = $this->request->getValue('companyName');

		    if(($user && $email != $user->email->get() && User::getInstanceByEmail($email)) || 
		       (!$user && User::getInstanceByEmail($email)))
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

	/**
	 * @return RequestValidator
	 */
    public static function createUserFormValidator(StoreManagementController $controller)
    {
        $validator = new RequestValidator("UserForm", $controller->request);		            
		
		$validator->addCheck('email', new IsNotEmptyCheck($controller->translate('_err_email_empty')));		
		$validator->addCheck('email', new IsValidEmailCheck($controller->translate('_err_invalid_email')));  
		$validator->addCheck('firstName', new IsNotEmptyCheck($controller->translate('_err_first_name_empty')));		
		$validator->addCheck('lastName', new IsNotEmptyCheck($controller->translate('_err_last_name_empty')));
		$validator->addCheck('password1', new PasswordEqualityCheck(
						                        $controller->translate('_err_passwords_are_not_the_same'), 
						                        $controller->request->getValue('password2'), 
												'password2'
					                        ));
					                        
		$validator->addCheck('password2', new PasswordEqualityCheck(
		                                        $controller->translate('_err_passwords_are_not_the_same'), 
		                                        $controller->request->getValue('password1'), 
												'password1'
	                                        ));

		$validator->addCheck('userGroupID', new IsNumericCheck($controller->translate('_err_invalid_group')));
		
		return $validator;
    }

    /**
     * @return Form
     * @role user.createUser
     */
	public static function createUserForm(StoreManagementController $controller, User $user = null)
	{
		$form = new Form(self::createUserFormValidator($controller));
		
		if($user)
		{
		    $form->setData($user->toFlatArray());
		}

		return $form;
	}

   
}
?>