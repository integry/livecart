<?php
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.delivery.State');
ClassLoader::import('application.model.user.*');

/**
 *  Handles user account related logic
 */
class UserController extends FrontendController
{
 	const PASSWORD_MIN_LENGTH = 5;
 	
 	const COUNT_RECENT_ORDERS = 5;
 
    public function init()
    {
        parent::init();  
        $this->addBreadCrumb($this->translate('_your_account'), $this->router->createUrl(array('controller' => 'user')));     }
    
    /**
     *	@role login
     */
	public function index()
    {		
		// get recent orders
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'ID'), 'DESC');
		$f->setLimit(self::COUNT_RECENT_ORDERS);
		
		$orders = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
		
		foreach ($orders as $order)
		{
            $order->loadAll();
        }

        $response = new ActionResponse();
		$response->setValue('user', $this->user->toArray());
		$response->setValue('orders', $orders->toArray());
		$response->setValue('userConfirm', $this->session->pullValue('userConfirm'));		
		return $response;
	}
    
    /**
     *	@role login
     */
    public function changePassword()
    {
        $this->addBreadCrumb($this->translate('_change_pass'), '');
        $response = new ActionResponse(); 
		$response->setValue('user', $this->user->toArray());
        $response->setValue('form', $this->buildPasswordChangeForm());        
        return $response;
    }
    
    /**
     *	@role login
     */
    public function doChangePassword()
    {
        $validator = $this->buildPasswordChangeValidator();
        if (!$validator->isValid())
        {
            return new ActionRedirectResponse('user', 'changePassword');
        }
        
        $this->user->setPassword($this->request->getValue('password'));
        $this->user->save();
        
        $this->session->setValue('userConfirm', $this->translate('_confirm_password_change'));
        
        return new ActionRedirectResponse('user', 'index');
    }
    
    /**
     *	@role login
     */
    public function changeEmail()
    {
        $this->addBreadCrumb($this->translate('_change_email'), '');
        $response = new ActionResponse(); 
		$response->setValue('user', $this->user->toArray());
        $response->setValue('form', $this->buildEmailChangeForm());        
        return $response;
    }

    /**
     *	@role login
     */
    public function doChangeEmail()
    {
        $validator = $this->buildEmailChangeValidator();
        if (!$validator->isValid())
        {
            return new ActionRedirectResponse('user', 'changeEmail');
        }
        
        $this->user->email->set($this->request->getValue('email'));
        $this->user->save();
        
        $this->session->setValue('userConfirm', $this->translate('_confirm_email_change'));
        
        return new ActionRedirectResponse('user', 'index');
    }
    
    /**
     *	@role login
     */
    public function addresses()
    {
        $this->addBreadCrumb($this->translate('_manage_addresses'), '');
        $response = new ActionResponse(); 
		$response->setValue('user', $this->user->toArray());
    	$response->setValue('billingAddresses', $this->user->getBillingAddressArray());
    	$response->setValue('shippingAddresses', $this->user->getShippingAddressArray());
        return $response;
    }    
    
    /**
     *	@role login
     */
    public function viewOrder()
    {
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $this->request->getValue('id')));
        $f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
        $f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
        
        $s = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
        if ($s->size())
        {
            $order = $s->get(0);
            $order->loadAll();  
            $response = new ActionResponse();
            $response->setValue('order', $order->toArray());
    		$response->setValue('user', $this->user->toArray());
            return $response; 
        }
        else
        {
            return new ActionRedirectResponse('user', 'index');   
        }           
    }

    /**
     *	@role login
     */
    public function orderInvoice()
    {
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $this->request->getValue('id')));
        $f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
        $f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
        
        $s = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
        if ($s->size())
        {
            $order = $s->get(0);
            $order->loadAll();  
            $response = new ActionResponse();
            $response->setValue('order', $order->toArray());
    		$response->setValue('user', $this->user->toArray());
            return $response; 
        }
        else
        {
            return new ActionRedirectResponse('user', 'index');   
        }           
    }

    public function register()
    {
		$response = new ActionResponse();
		$response->setValue('regForm', $this->buildRegForm());				
		return $response;		
	}
    
    public function doRegister()
    {
		if (!$this->buildRegValidator()->isValid())
		{
			return new ActionRedirectResponse('user', 'register');
		}
		
		$user = $this->createUser($this->request->getValue('password'));
		$user->setAsCurrentUser();
		
		if ($this->request->isValueSet('return'))
		{
			return new RedirectResponse(Router::getInstance()->createUrlFromRoute($this->request->getValue('return')));
		}
		else
		{
			return new ActionRedirectResponse('user', 'index');
		}
	}
    
	/**
     *  Login form
     */
    public function login()
    {
		$response = new ActionResponse();
        $response->setValue('regForm', $this->buildRegForm());				
        $response->setValue('email', $this->request->getValue('email'));
		return $response;
    }
    
    /**
     *  Process actual login
     */
    public function doLogin()
    {
        $user = User::getInstanceByLogin($this->request->getValue('email'), $this->request->getValue('password'));
        if (!$user)
        {
            return new ActionRedirectResponse('user', 'login', array('query' => 'failed=true'));
        }

        $user->setAsCurrentUser();
        
        // load the last un-finalized order by this user
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $user->getID()));
        $f->mergeCondition(new NotEqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
        $f->setOrder(new ARFieldHandle('CustomerOrder', 'dateCreated'), 'DESC');
		$f->setLimit(1);
        $s = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);

		$sessionOrder = CustomerOrder::getInstance();			
        if ($s->size())
        {
			$order = $s->get(0);
			if ($sessionOrder->getID() != $order->getID())
			{
				$order->loadItems();
				$order->merge($sessionOrder);
				$order->saveToSession();
				$sessionOrder->delete();
			}
		}
		else
		{
			if ($sessionOrder->getID())
			{
				$sessionOrder->user->set($user);
				$sessionOrder->saveToSession();
			}
		}
        
        return new RedirectResponse($this->request->getValue('return'));
    }
    
    public function remindPassword()
    {
		$response = new ActionResponse();
		
		return $response;
	}
	
	public function logout()
    {
		Session::getInstance()->unsetValue('User');
		return new ActionRedirectResponse('index', 'index');
	}
    
    public function checkout()
    {
        $form = $this->buildForm();
                                
        $form->setValue('billing_country', $this->config->getValue('DEF_COUNTRY'));  
                                
        $response = new ActionResponse();   
        $response->setValue('form', $form);
        $response->setValue('countries', $this->getCountryList($form));
        $response->setValue('states', $this->getStateList($form->getValue('billing_country')));
        return $response;
    }        
    
    public function processCheckoutRegistration()
    {
        ActiveRecordModel::beginTransaction();
        
        $validator = $this->buildValidator();
        if (!$validator->isValid())
        {
            return new ActionRedirectResponse('user', 'checkout');
        }

        // create user account
        $user = $this->createUser();
        
        // get billing address state
        if ($this->request->getValue('billing_state_select'))
        {
            try
            {
                $billingState = ActiveRecordModel::getInstanceByID('State', $this->request->getValue('billing_state_select'), ActiveRecordModel::LOAD_DATA);
            }
            catch (Exception $e)
            {
                throw new ApplicationException('State not found');   
            }               
            
            $billingCountry = $billingState->countryID->get();
        }

        // create user billing addresses
        $address = UserAddress::getNewInstance();
        $address->firstName->set($user->firstName->get());
        $address->lastName->set($user->lastName->get());
        $address->companyName->set($user->companyName->get());
        $address->address1->set($this->request->getValue('billing_address1'));        
        $address->address2->set($this->request->getValue('billing_address2'));        
        $address->city->set($this->request->getValue('billing_city'));
        $address->countryID->set($this->request->getValue('billing_country'));
        $address->postalCode->set($this->request->getValue('billing_zip'));
        $address->phone->set($this->request->getValue('phone'));
        if (isset($billingState))
        {
            $address->state->set($billingState);
        }
        else
        {
            $address->stateName->set($this->request->getValue('billing_state_text'));
        }
        $address->save();
        
		$billingAddress = BillingAddress::getNewInstance($user, $address);
        $billingAddress->save();
        
        // create user shipping address
        if ($this->request->getValue('sameAsBilling'))
        {
			$address = clone $address;
		}
		else
		{
	        $address = UserAddress::getNewInstance();
	        $address->name->set($user->name->get());
	        $address->address1->set($this->request->getValue('shipping_address1'));
	        $address->address2->set($this->request->getValue('shipping_address2'));
		}

	    $address->save();
		$shippingAddress = ShippingAddress::getNewInstance($user, $address);
        $shippingAddress->save();

        $user->defaultShippingAddress->set($shippingAddress);
        $user->defaultBillingAddress->set($billingAddress);
        $user->save();
        
        // set order addresses
        $order = CustomerOrder::getInstance();
        $order->billingAddress->set($billingAddress->userAddress->get());
        $order->shippingAddress->set($shippingAddress->userAddress->get());
        $order->user->set($user);
        $order->save();
        
        $user->setAsCurrentUser();       

        ActiveRecordModel::commit();

        return new ActionRedirectResponse('checkout', 'shipping');
    }

    /**
     *	@role login
     */
    public function deleteShippingAddress()
    {
        try
        {
            return $this->deleteAddress(ShippingAddress::getUserAddress($this->request->getValue('id'), $this->user));
        }
        catch (ARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }
    }

    /**
     *	@role login
     */
    public function deleteBillingAddress()
    {
        try
        {
            return $this->deleteAddress(BillingAddress::getUserAddress($this->request->getValue('id'), $this->user));
        }
        catch (ARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }
    }
    
    private function deleteAddress(UserAddressType $address)
    {
        $address->delete();
        return new RedirectResponse(Router::getInstance()->createURLFromRoute($this->request->getValue('return')));      
    }
    
    /**
     *	@role login
     */
    public function editShippingAddress()
    {
        try
        {
            return $this->editAddress(ShippingAddress::getUserAddress($this->request->getValue('id'), $this->user));
        }
        catch (ARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }
    }

    /**
     *	@role login
     */
    public function editBillingAddress()
    {
        try
        {
            return $this->editAddress(BillingAddress::getUserAddress($this->request->getValue('id'), $this->user));
        }
        catch (ARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }
    }

    private function editAddress(UserAddressType $addressType)
    {        
        $form = $this->buildAddressForm();
        $address = $addressType->userAddress->get();
        
        $form->setData($address->toArray());
        $form->setValue('country', $address->countryID->get());
        $form->setValue('state_text', $address->stateName->get());
        
        if ($address->state->get())
        {
            $form->setValue('state_select', $address->state->get()->getID());
        }

        $form->setValue('zip', $address->postalCode->get());
                        
        $response = new ActionResponse();        
        $response->setValue('form', $form);
        $response->setValue('return', $this->request->getValue('return'));
        $response->setValue('countries', $this->getCountryList($form));
        $response->setValue('states', $this->getStateList($form->getValue('country')));
        $response->setValue('address', $address->toArray());
        $response->setValue('addressType', $addressType->toArray());
        return $response;            
    }
    
    /**
     *	@role login
     */
    public function saveShippingAddress()
    {
        try
        {
            $address = ShippingAddress::getUserAddress($this->request->getValue('id'), $this->user);
        }
        catch (zzzARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }

        return $this->doSaveAddress($address, new ActionRedirectResponse('user', 'editShippingAddress', array('id' =>$this->request->getValue('id'), 'query' => array('return' => $this->request->getValue('return')))));        
    }
    
    /**
     *	@role login
     */
    public function saveBillingAddress()
    {
        try
        {
            $address = BillingAddress::getUserAddress($this->request->getValue('id'), $this->user);
        }
        catch (ARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }

        return $this->doSaveAddress($address, new ActionRedirectResponse('user', 'editBillingAddress', array('id' =>$this->request->getValue('id'), 'query' => array('return' => $this->request->getValue('return')))));        
    }
    
    private function doSaveAddress(UserAddressType $address, ActionRedirectResponse $invalidResponse)
    {
        $address = $address->userAddress->get();
        if ($this->buildAddressValidator()->isValid())
        {
            $this->saveAddress($address);
            return new RedirectResponse(Router::getInstance()->createURLFromRoute($this->request->getValue('return')));
        }
        else
        {
            return $invalidResponse;
        }        
    }
    
    /**
     *	@role login
     */
    public function addBillingAddress()
    {
        $form = $this->buildAddressForm();
        
        $form->setValue('firstName', $this->user->firstName->get());
        $form->setValue('lastName', $this->user->lastName->get());
        $form->setValue('companyName', $this->user->companyName->get());

		$this->user->loadAddresses();

        if ($this->user->defaultBillingAddress->get())
        {
			$form->setValue('country', $this->user->defaultBillingAddress->get()->userAddress->get()->countryID->get());
	        $form->setValue('phone', $this->user->defaultBillingAddress->get()->userAddress->get()->phone->get());			
		}
		else
		{
			$form->setValue('country', $this->config->getValue('DEF_COUNTRY'));	
		}
                
        $response = new ActionResponse();        
        $response->setValue('form', $form);
        $response->setValue('return', $this->request->getValue('return'));
        $response->setValue('countries', $this->getCountryList($form));
        $response->setValue('states', $this->getStateList($form->getValue('country')));
        return $response;    
    }
    
    /**
     *	@role login
     */
    public function addShippingAddress()
    {
        return $this->addBillingAddress();
    }

    /**
     *	@role login
     */
    public function doAddBillingAddress()
    {       
        return $this->doAddAddress('BillingAddress', new ActionRedirectResponse('user', 'addBillingAddress', array('query' => array('return' => $this->request->getValue('return')))));
    }

    /**
     *	@role login
     */
    public function doAddShippingAddress()
    {       
        return $this->doAddAddress('ShippingAddress', new ActionRedirectResponse('user', 'addShippingAddress', array('query' => array('return' => $this->request->getValue('return')))));
    }

    /**
     *  Return a list of states for the selected country
     *  @return JSONResponse
     */
    public function states()
    {                
        $states = State::getStatesByCountry($this->request->getValue('country'));
        return new JSONResponse($states);
    }
    
    /**
     *	@return User
     */
	private function createUser($password = '')
    {
		$user = User::getNewInstance($this->request->getValue('email'), $this->request->getValue('password'));
        $user->firstName->set($this->request->getValue('firstName'));
        $user->lastName->set($this->request->getValue('lastName'));
        $user->companyName->set($this->request->getValue('companyName'));
        $user->email->set($this->request->getValue('email'));
        $user->isEnabled->set(true);
        
        if ($password)
        {
			$user->setPassword($password);
		}
		
		$user->save();
		
		return $user;			
	}

    private function doAddAddress($addressClass, Response $failureResponse)
    {
        $validator = $this->buildAddressValidator();
        if ($validator->isValid())
        {
            $address = UserAddress::getNewInstance();
            $this->saveAddress($address);
            
            $addressType = call_user_func_array(array($addressClass, 'getNewInstance'), array($this->user, $address));
            $addressType->save();
            
            if ($this->request->getValue('return'))
            {
                $response = new RedirectResponse(Router::getInstance()->createURLFromRoute($this->request->getValue('return')));    
            }
            else
            {
                $response = new ActionRedirectResponse('user', 'addresses');   
            }               
            
            return $response;
        }
        else
        {
            return $failureResponse;
        }        
    }

    private function saveAddress(UserAddress $address)
    {
        $address->loadRequestData($this->request);
        $address->countryID->set($this->request->getValue('country'));
        $address->postalCode->set($this->request->getValue('zip'));
        $address->stateName->set($this->request->getValue('state_text')); 
        if ($this->request->getValue('state_select'))
        {
            $address->state->set(State::getStateByIDAndCountry($this->request->getValue('state_select'), $this->request->getValue('country')));                
        }
        else
        {
            $address->state->set(null);   
        }
        $address->save();        
    }
    
    private function getCountryList(Form $form)
    {
        $defCountry = $this->config->getValue('DEF_COUNTRY');

        $countries = $this->store->getEnabledCountries();
        asort($countries);        
    
        // set default country first
        if (isset($countries[$defCountry]))
        {
            $d = $countries[$defCountry];
            unset($countries[$defCountry]);
            $countries = array_merge(array($defCountry => $d), $countries);
        }        

        return $countries;        
    }
    
    private function getStateList($country)
    {
        $states = State::getStatesByCountry($country);
        
        if ($states)
        {
            $states = array('' => '') + $states;            
        }
        
        return $states;        
    }        
    
	/**************  VALIDATION ******************/
    private function buildEmailChangeForm()
    {
		ClassLoader::import("framework.request.validator.Form");		
		return new Form($this->buildEmailChangeValidator()); 
	}
    
    private function buildEmailChangeValidator()
    {    
		ClassLoader::import("framework.request.validator.RequestValidator");		
		
        $validator = new RequestValidator("emailChange", $this->request);
        $this->validateEmail($validator, '_err_not_unique_email_for_change');
    	
    	return $validator;
    }
    
    private function buildPasswordChangeForm()
    {
		ClassLoader::import("framework.request.validator.Form");		
		return new Form($this->buildPasswordChangeValidator()); 
	}
    
    private function buildPasswordChangeValidator()
    {    
		ClassLoader::import("framework.request.validator.RequestValidator");		
		ClassLoader::import("application.helper.check.IsPasswordCorrectCheck");
		
        $validator = new RequestValidator("passwordChange", $this->request);
    	$validator->addCheck('currentpassword', new IsNotEmptyCheck($this->translate('_err_enter_current_password'))); 
    	$validator->addCheck('currentpassword', new IsPasswordCorrectCheck($this->translate('_err_incorrect_current_password'), $this->user)); 
        
        $this->validatePassword($validator);
    	
    	return $validator;
    }

    private function buildRegForm()
    {
		ClassLoader::import("framework.request.validator.Form");		
		return new Form($this->buildRegValidator()); 
	}
    
    private function buildRegValidator()
    {    
		ClassLoader::import("framework.request.validator.RequestValidator");
		            	
        $validator = new RequestValidator("userRegistration", $this->request);
        $this->validateName($validator);
        $this->validateEmail($validator);
        $this->validatePassword($validator);
        return $validator;
    }

    private function buildAddressForm()
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildAddressValidator());        
    }

    private function buildAddressValidator()
    {    
		ClassLoader::import("framework.request.validator.RequestValidator");
            	
        $validator = new RequestValidator("userAddress", $this->request);
        $this->validateAddress($validator);        
        return $validator;
    }

    private function buildForm()
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator());        
    }
    
    private function buildValidator()
    {    
		ClassLoader::import("framework.request.validator.RequestValidator");
            	
        // validate contact info
        $validator = new RequestValidator("registrationValidator", $this->request);
		$this->validateEmail($validator);
	                    	
        // validate billing info
    	$this->validateAddress($validator, 'billing_');

    	// validate shipping address
    	$shippingCondition = new ShippingAddressCheckCondition($this->request);
        $validator->addCheck('shipping_address1', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_enter_address'))));
        $validator->addCheck('shipping_city', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_enter_city'))));
        $validator->addCheck('shipping_country', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_select_country'))));
        $validator->addCheck('shipping_zip', new ConditionalCheck($shippingCondition, new IsNotEmptyCheck($this->translate('_err_enter_zip'))));
                        
        $stateCheck = new OrCheck(array('shipping_state_select', 'shipping_state_text'), array(new IsNotEmptyCheck($this->translate('_err_select_state')), new IsNotEmptyCheck('')), $this->request);
        $validator->addCheck('shipping_state_select', new ConditionalCheck($shippingCondition, $stateCheck));
//        $validator->addCheck('billing_state_select', new IsValidStateCheck($this->translate('_err_select_state')));

//        $validator->addConditionalCheck($shippingCondition, )
        
        return $validator;    
    }
    
    private function validateName(RequestValidator $validator)
    {
    	$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_err_enter_first_name')));
    	$validator->addCheck('lastName', new IsNotEmptyCheck($this->translate('_err_enter_last_name')));
	}    
    
    private function validateEmail(RequestValidator $validator, $uniqueError = '_err_not_unique_email')
    {
		ClassLoader::import("application.helper.check.IsUniqueEmailCheck");

    	$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_enter_email')));    
    	$validator->addCheck('email', new IsValidEmailCheck($this->translate('_err_invalid_email')));    
    	
        $emailErr = $this->translate($uniqueError);
        $emailErr = str_replace('%1', Router::getInstance()->createUrl(array('controller' => 'user', 'action' => 'login', 'query' => array('email' => $this->request->getValue('email')))), $emailErr);
        $validator->addCheck('email', new IsUniqueEmailCheck($emailErr));    
	}    
	
    private function validateAddress(RequestValidator $validator, $fieldPrefix = '')
    {
		$this->validateName($validator);
		
        if ($this->config->getValue('REQUIRE_PHONE'))
        {
            $validator->addCheck('phone', new IsNotEmptyCheck($this->translate('_err_enter_phone')));
        }

        $validator->addCheck($fieldPrefix . 'address1', new IsNotEmptyCheck($this->translate('_err_enter_address')));
        $validator->addCheck($fieldPrefix . 'city', new IsNotEmptyCheck($this->translate('_err_enter_city')));
        $validator->addCheck($fieldPrefix . 'country', new IsNotEmptyCheck($this->translate('_err_select_country')));
        $validator->addCheck($fieldPrefix . 'zip', new IsNotEmptyCheck($this->translate('_err_enter_zip')));
                        
        $stateCheck = new OrCheck(array($fieldPrefix . 'state_select', $fieldPrefix . 'state_text'), array(new IsNotEmptyCheck($this->translate('_err_select_state')), new IsNotEmptyCheck('')), $this->request);
        $validator->addCheck($fieldPrefix . 'state_select', $stateCheck);
//        $validator->addCheck('billing_state_select', new IsValidStateCheck($this->translate('_err_select_state')));        
    }
    
    private function validatePassword(RequestValidator $validator)
    {
		ClassLoader::import("application.helper.check.PasswordMatchCheck");
    	$validator->addCheck('password', new MinLengthCheck(sprintf($this->translate('_err_short_password'), self::PASSWORD_MIN_LENGTH), self::PASSWORD_MIN_LENGTH)); 
    	$validator->addCheck('password', new IsNotEmptyCheck($this->translate('_err_enter_password'))); 
    	$validator->addCheck('confpassword', new IsNotEmptyCheck($this->translate('_err_enter_password'))); 
    	$validator->addCheck('confpassword', new PasswordMatchCheck($this->translate('_err_password_match'), $this->request, 'password', 'confpassword'));             
    }
}
 
ClassLoader::import('framework.request.validator.check.CheckCondition');

class ShippingAddressCheckCondition extends CheckCondition
{
    function isSatisfied()
    {
        return !$this->request->isValueSet('sameAsBilling');
    }
}
    
?>