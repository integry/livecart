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
        $user->loadAddresses();
	    $response->set('user', $user->toArray());
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
		
		$userArray = array();
        $userArray['sameAddresses'] = 1;
		if($user)
		{
		    $userArray = array_merge($userArray, $user->toFlatArray());
		    
		    if($user->defaultShippingAddress->get())
		    {
                $user->defaultShippingAddress->get()->load(array('UserAddress'));
                foreach($user->defaultShippingAddress->get()->userAddress->get()->toFlatArray() as $property => $value)
                {
                    $userArray["shippingAddress_" . $property] = $value;
                }
		    }

		    if($user->defaultBillingAddress->get())
		    {
                $user->defaultBillingAddress->get()->load(array('UserAddress'));
                foreach($user->defaultBillingAddress->get()->userAddress->get()->toArray() as $property => $value)
                {
                    $userArray["billingAddress_" . $property] = $value;
                }
		    }
		    
		    if($user->defaultShippingAddress->get()->userAddress->get()->getID() != $user->defaultBillingAddress->get()->userAddress->get()->getID())
		    {
		        $userArray['sameAddresses'] = 0;
		    }
		    
		    $form->setData($userArray);
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
			
            $user->loadAddresses();
            
            // Billing address          
            if(!$user->defaultBillingAddress->get())
            {
                $address = UserAddress::getNewInstance();
            } 
            else
            {
                $address = $user->defaultBillingAddress->get()->userAddress->get();
            }
            
            $address->firstName->set($this->request->get("billingAddress_firstName"));
            $address->lastName->set($this->request->get("billingAddress_lastName"));
            $address->companyName->set($this->request->get("billingAddress_companyName"));
            $address->countryID->set($this->request->get("billingAddress_countryID"));
            $address->stateID->set($this->request->get("billingAddress_stateID"));
            $address->stateName->set($this->request->get("billingAddress_stateName"));
            $address->city->set($this->request->get("billingAddress_city"));
            $address->address1->set($this->request->get("billingAddress_address1"));
            $address->address2->set($this->request->get("billingAddress_address2"));
            $address->postalCode->set($this->request->get("billingAddress_postalCode"));
            $address->phone->set($this->request->get("billingAddress_phone"));
            
            $address->save();
            
            if(!$user->defaultBillingAddress->get())
            {
                $billingAddress = BillingAddress::getNewInstance($user, $address);
                $billingAddress->save();
            }
            
            // Shipping address   
            if(!$user->defaultBillingAddress->get() && $this->request->get('sameAddresses'))
            {
                $address = clone $address;
                $shippingAddress = BillingAddress::getNewInstance($user, $address);
                $shippingAddress->save();
            } 
            else if(!$this->request->get('sameAddresses'))
            {
                if(!$user->defaultShippingAddress->get())
                {
                    $address = UserAddress::getNewInstance();
                }
                else
                {
                    $address = $user->defaultShippingAddress->get()->userAddress->get();
                }

                $address->firstName->set($this->request->get("shippingAddress_firstName"));
                $address->lastName->set($this->request->get("shippingAddress_lastName"));
                $address->companyName->set($this->request->get("shippingAddress_companyName"));
                $address->countryID->set($this->request->get("shippingAddress_countryID"));
                $address->stateID->set($this->request->get("shippingAddress_stateID"));
                $address->stateName->set($this->request->get("shippingAddress_stateName"));
                $address->city->set($this->request->get("shippingAddress_city"));
                $address->address1->set($this->request->get("shippingAddress_address1"));
                $address->address2->set($this->request->get("shippingAddress_address2"));
                $address->postalCode->set($this->request->get("shippingAddress_postalCode"));
                $address->phone->set($this->request->get("shippingAddress_phone"));
                
                $address->save();
            
                if(!$user->defaultShippingAddress->get())
                {
                    $shippingAddress = ShippingAddress::getNewInstance($user, $address);
                    $shippingAddress->save();
                }
            }
			
			return new JSONResponse(array('user' => $user->toArray()), 'success', $this->translate('_user_details_were_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_details'));
		}
	}
}
?>