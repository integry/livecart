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
        $response->set('countries', $this->application->getEnabledCountries());
        $form = self::createUserForm($this, $user);
        $response->set('form', $form);
        $response->set('shippingAddressStates', State::getStatesByCountry($form->get('shippingAddress_countryID')));
        $response->set('billingAddressStates', State::getStatesByCountry($form->get('billingAddress_countryID')));
        $user->loadAddresses();
	    $response->set('someUser', $user->toArray());
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
        
        $user->loadAddresses();
        
        return $this->save($user);
    }

	/**
	 * @return RequestValidator
	 */
    public static function createUserFormValidator(StoreManagementController $controller, $user = false)
    {
        $validator = new RequestValidator("UserForm", $controller->request);		            
		
		$validator->addCheck('email', new IsNotEmptyCheck($controller->translate('_err_email_empty')));		
		$validator->addCheck('email', new IsValidEmailCheck($controller->translate('_err_invalid_email')));  
		$validator->addCheck('firstName', new IsNotEmptyCheck($controller->translate('_err_first_name_empty')));		
		$validator->addCheck('lastName', new IsNotEmptyCheck($controller->translate('_err_last_name_empty')));
		
		
        $passwordLengthStart = 6;
        $passwordLengthEnd = 30;
		$allowEmpty = $user;
		
        $validator->addCheck('password', 
            new IsLengthBetweenCheck(
                sprintf($controller->translate('_err_password_lenght_should_be_in_interval'), $passwordLengthStart, $passwordLengthEnd), 
                $passwordLengthStart, $passwordLengthEnd, $allowEmpty
            ));
		
		$validator->addCheck('userGroupID', new IsNumericCheck($controller->translate('_err_invalid_group')));
		
		return $validator;
    }

    public function generatePassword()
    {
        ClassLoader::import("library.text.Password");
        return new RawResponse(Password::create(10, Password::MIX));
    }
    
    /**
     * @return Form
     */
	public static function createUserForm(StoreManagementController $controller, User $user = null)
	{
		$form = new Form(self::createUserFormValidator($controller, $user));
		
		$userArray = array();
		if($user)
		{
		    $userArray = array_merge($userArray, $user->toFlatArray());
		    
		    $user->loadAddresses();
		    
		    if($user->defaultShippingAddress->get())
		    {
                $user->defaultShippingAddress->get()->load(array('UserAddress'));
                $shippingArray = $user->defaultShippingAddress->get()->userAddress->get()->toArray();
                $shippingFlatArray = $user->defaultShippingAddress->get()->userAddress->get()->toFlatArray();
                foreach($shippingFlatArray as $property => $value)
                {
                    if($property == 'State') $property = 'stateID';
                    $userArray["shippingAddress_" . $property] = $value;
                }
		    }
		    
		    if($user->defaultBillingAddress->get())
		    {
                $user->defaultBillingAddress->get()->load(array('UserAddress'));
                $billingArray = $user->defaultBillingAddress->get()->userAddress->get()->toArray();
                $billingFlatArray = $user->defaultBillingAddress->get()->userAddress->get()->toFlatArray();
                foreach($billingFlatArray as $property => $value)
                {
                    if($property == 'State') $property = 'stateID';
                    $userArray["billingAddress_" . $property] = $value;
                }
		    }
		    
		    if($user->defaultBillingAddress->get() || 
		    $user->defaultBillingAddress->get() || 
		    (array_diff_key($shippingFlatArray, array('ID' => 0)) == array_diff_key($billingFlatArray, array('ID' => 0))))
		    {
		        $userArray['sameAddresses'] = 1;
		    }
		    
		}
		else
		{
            $userArray['sameAddresses'] = 1;
		}
		
        $form->setData($userArray);
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
            
			if ('delete' != $act)
            {
                $user->save();
            }   
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
		    $password = $this->request->get('password');
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
			
			$this->saveAddresses($user);
			
			return new JSONResponse(array('user' => $user->toArray()), 'success', $this->translate('_user_details_were_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_details'));
		}
	}
	
	function saveAddresses(User $user = null)
	{
	    $address1 = null;
	    $address2 = null;
	    
        $user->loadAddresses();
        // Billing address          
        if(!$user->defaultBillingAddress->get())
        {
            $address1 = UserAddress::getNewInstance();
        } 
        else
        {
            $address1 = $user->defaultBillingAddress->get()->userAddress->get();
        }
        
        $address1->firstName->set($this->request->get("billingAddress_firstName"));
        $address1->lastName->set($this->request->get("billingAddress_lastName"));
        $address1->companyName->set($this->request->get("billingAddress_companyName"));
        $address1->countryID->set($this->request->get("billingAddress_countryID"));
        $address1->city->set($this->request->get("billingAddress_city"));
        $address1->address1->set($this->request->get("billingAddress_address1"));
        $address1->address2->set($this->request->get("billingAddress_address2"));
        $address1->postalCode->set($this->request->get("billingAddress_postalCode"));
        $address1->phone->set($this->request->get("billingAddress_phone"));
        
        // get billing address state
        if ($stateID = $this->request->get("billingAddress_stateID"))
        {
            $address1->state->set(ActiveRecordModel::getInstanceByID('State', $stateID, ActiveRecordModel::LOAD_DATA));
            $address1->stateName->setNull();
        } 
        else
        {
            $address1->stateName->set($this->request->get("billingAddress_stateName"));
            $address1->state->setNull();
        }
        
        
        if(!$user->defaultShippingAddress->get() && $this->request->get('sameAddresses'))
        {
            $address2 = clone $address1;
        }
        
        $address1->save();
        
        if(!$user->defaultBillingAddress->get())
        {
            $billingAddress = BillingAddress::getNewInstance($user, $address1);
            $billingAddress->save();
        }
        
        // Shipping address
        if(!$user->defaultShippingAddress->get() && $this->request->get('sameAddresses'))
        {
            $address2->save();
            $shippingAddress = ShippingAddress::getNewInstance($user, $address2);
            $shippingAddress->save();
        } 
        else
        {
            if(!$user->defaultShippingAddress->get() || ($user->defaultShippingAddress->get()->userAddress->get() == $user->defaultBillingAddress->get()->userAddress->get()))
            {
                $address2 = UserAddress::getNewInstance();
            }
            else
            {
                $address2 = $user->defaultShippingAddress->get()->userAddress->get();
            }
        
            $address2->firstName->set($this->request->get("shippingAddress_firstName"));
            $address2->lastName->set($this->request->get("shippingAddress_lastName"));
            $address2->companyName->set($this->request->get("shippingAddress_companyName"));
            $address2->countryID->set($this->request->get("shippingAddress_countryID"));   
            $address2->city->set($this->request->get("shippingAddress_city"));
            $address2->address1->set($this->request->get("shippingAddress_address1"));
            $address2->address2->set($this->request->get("shippingAddress_address2"));
            $address2->postalCode->set($this->request->get("shippingAddress_postalCode"));
            $address2->phone->set($this->request->get("shippingAddress_phone"));
                                
            // get shipping address state
            if ($stateID = $this->request->get("shippingAddress_stateID"))
            {
                $address2->state->set(ActiveRecordModel::getInstanceByID('State', $stateID, ActiveRecordModel::LOAD_DATA));
                $address2->stateName->setNull();
            } 
            else
            {
                $address2->stateName->set($this->request->get("shippingAddress_stateName"));
                $address2->state->setNull();
            }
            
            $address2->save();
        
            if(!$user->defaultShippingAddress->get())
            {
                $shippingAddress = ShippingAddress::getNewInstance($user, $address2);
                $shippingAddress->save();
            }
        }
	}
}
?>