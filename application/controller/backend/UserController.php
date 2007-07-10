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
	public function info()
	{
	    $user = User::getInstanceById((int)$this->request->get('id'), ActiveRecord::LOAD_DATA, array('UserGroup'));
		
        $availableUserGroups = array('' => '');
        foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
        {
            $availableUserGroups[$group->getID()] = $group->name->get();
        }
        
	    $response = new ActionResponse();	    
	    $response->set('user', $user->toFlatArray());
	    $response->set('availableUserGroups', $availableUserGroups);
	    $response->set('form', self::createUserForm($this, $user));
		
		return $response;
	}	

    /**
     * @role create
     */
    public function create()
    {
        return $this->save(null);
    }
    
    /**
     * @role update
     */
    public function update()
    {
        $user = User::getInstanceByID((int)$this->request->get('id'), true);
        return $this->save($user);
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
						                        $controller->request->get('password2'), 
												'password2'
					                        ));
					                        
		$validator->addCheck('password2', new PasswordEqualityCheck(
		                                        $controller->translate('_err_passwords_are_not_the_same'), 
		                                        $controller->request->get('password1'), 
												'password1'
	                                        ));

		$validator->addCheck('userGroupID', new IsNumericCheck($controller->translate('_err_invalid_group')));
		
		return $validator;
    }

    /**
     * @return Form
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

	
	/**
	 * @role mass
	 */
    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
		$filters = (array)json_decode($this->request->get('filters'));
		$this->request->set('filters', $filters);
		
        $grid = new ActiveGrid($this->application, $filter, 'User');
        $filter->setLimit(0);
        					
		$users = ActiveRecordModel::getRecordSet('User', $filter, User::LOAD_REFERENCES);
		
        $act = $this->request->get('act');
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
		
		return new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->translate('_mass_action_succeed'));	
    } 
	

    /**
     *  Return a list of states for the selected country
     *  @return JSONResponse
     */
    public function states()
    {                
        $states = State::getStatesByCountry($this->request->get('country'));
        return new JSONResponse($states);
    }
    
	private function save(User $user = null)
	{
   		$validator = self::createUserFormValidator($this, $user);
		if ($validator->isValid())
		{
		    $email = $this->request->get('email');
		    $password = $this->request->get('password1');
		    $firstName = $this->request->get('firstName');
		    $lastName = $this->request->get('lastName');
		    $companyName = $this->request->get('companyName');

		    if(($user && $email != $user->email->get() && User::getInstanceByEmail($email)) || 
		       (!$user && User::getInstanceByEmail($email)))
		    {
		        return new JSONResponse(false, 'failure', $this->translate('_err_this_email_is_already_being_used_by_other_user'));
		    }
		    
		    if($groupID = (int)$this->request->get('UserGroup'))
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
			
			if(!empty($password))
			{
			    $user->setPassword($password);
			}
			
			$user->companyName->set($companyName);
			$user->email->set($email);
			$user->userGroup->set($group);
			
			$user->save();
			
			return new JSONResponse(array('user' => $user->toArray()), 'success', $this->translate('_user_details_were_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_details'));
		}
	}
}
?>