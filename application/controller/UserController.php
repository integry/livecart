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
    /**
     *  Login form
     */
    public function login()
    {

    }
    
    /**
     *  Process actual login
     */
    public function processLogin()
    {
        $user = User::getInstanceByLogin($this->request->getValue('email'), $this->request->getValue('password'));
        if ($user)
        {
            $this->loginUser($user); 
            return new RedirectResponse($this->request->getValue('return'));               
        }
        else
        {
            return new ActionRedirectResponse('user', 'login', array('query' => 'failed=true'));
        }
    }
    
    public function checkout()
    {
        $form = $this->buildForm();
                                
        $form->setValue('billing_country', $defCountry);                                
                                
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
        $user = User::getNewInstance($this->request->getValue('email'), $this->request->getValue('password'));
        $user->firstName->set($this->request->getValue('firstName'));
        $user->lastName->set($this->request->getValue('lastName'));
        $user->companyName->set($this->request->getValue('companyName'));
        $user->email->set($this->request->getValue('email'));
        $user->isEnabled->set(true);
        $user->save();
        
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

        return new ActionRedirectResponse('checkout', 'confirmTotals');
    }

    public function editShippingAddress()
    {
        try
        {
            ShippingAddress::getUserAddress($this->request->getValue('id'), $this->user);
        }
        catch (ARNotFoundException $e)
        {
            return new ActionRedirectResponse('user', 'index');   
        }
    }

    public function addBillingAddress()
    {
        $form = $this->buildAddressForm();
        
        $form->setValue('country', $this->user->defaultBillingAddress->get()->userAddress->get()->countryID->get());
        $form->setValue('firstName', $this->user->firstName->get());
        $form->setValue('lastName', $this->user->lastName->get());
        $form->setValue('companyName', $this->user->companyName->get());
        $form->setValue('phone', $this->user->defaultBillingAddress->get()->userAddress->get()->phone->get());
                
        $response = new ActionResponse();        
        $response->setValue('form', $form);
        $response->setValue('return', $this->request->getValue('return'));
        $response->setValue('countries', $this->getCountryList($form));
        $response->setValue('states', $this->getStateList($form->getValue('country')));
        return $response;    
    }
    
    public function addShippingAddress()
    {
        return $this->addBillingAddress();
    }

    public function doAddBillingAddress()
    {       
        return $this->doAddAddress('BillingAddress', new ActionRedirectResponse('user', 'addBillingAddress', array('query' => array('return' => $this->request->getValue('return')))));
    }

    public function doAddShippingAddress()
    {       
        return $this->doAddAddress('ShippingAddress', new ActionRedirectResponse('user', 'addShippingAddress', array('query' => array('return' => $this->request->getValue('return')))));
    }

    private function doAddAddress($addressClass, Response $failureResponse)
    {
        $validator = $this->buildAddressValidator();
        if ($validator->isValid())
        {
            $address = UserAddress::getNewInstance();
            $address->firstName->set($this->request->getValue('firstName'));
            $address->lastName->set($this->request->getValue('lastName'));
            $address->companyName->set($this->request->getValue('companyName'));
            $address->address1->set($this->request->getValue('address1'));        
            $address->address2->set($this->request->getValue('address2'));        
            $address->city->set($this->request->getValue('city'));
            $address->countryID->set($this->request->getValue('country'));
            $address->postalCode->set($this->request->getValue('zip'));
            $address->phone->set($this->request->getValue('phone'));            
            $address->save();
            
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

    /**
     *  Return a list of states for the selected country
     *  @return JSONResponse
     */
    public function states()
    {                
        return new JSONResponse(State::getStatesByCountry($this->request->getValue('country')));  
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
		ClassLoader::import("application.helper.check.IsUniqueEmailCheck");
            	
        // validate contact info
        $validator = new RequestValidator("registrationValidator", $this->request);
    	$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_enter_email')));    
    	
        $emailErr = $this->translate('_err_not_unique_email');
        $emailErr = str_replace('%1', Router::getInstance()->createUrl(array('controller' => 'user', 'action' => 'login')), $emailErr);
        $validator->addCheck('email', new IsUniqueEmailCheck($emailErr));    
                    	
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
    
    private function validateAddress(RequestValidator $validator, $fieldPrefix = '')
    {
    	$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_err_enter_first_name')));
    	$validator->addCheck('lastName', new IsNotEmptyCheck($this->translate('_err_enter_last_name')));

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
        $states = array_merge(array('' => ''), $states);
        
        return $states;        
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