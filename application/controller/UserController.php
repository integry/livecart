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
    public function checkout()
    {
        $defCountry = $this->config->getValue('DEF_COUNTRY');
        
        $form = $this->buildForm();
        $form->setValue('billing_country', $defCountry);

        $countries = $this->store->getEnabledCountries();
        asort($countries);        
    
        // set default country first
        if (isset($countries[$defCountry]))
        {
            $d = $countries[$defCountry];
            unset($countries[$defCountry]);
            $countries = array_merge(array($defCountry => $d), $countries);
        }        
        
        $states = State::getStatesByCountry($form->getValue('billing_country'));
        $states = array_merge(array('' => ''), $states);
                
        $response = new ActionResponse();   
        $response->setValue('form', $form);
        $response->setValue('countries', $countries);
        $response->setValue('states', $states);
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
        $address->name->set($user->getName());
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
        
		$userBillingAddress = UserBillingAddress::getNewInstance($user, $address);
        $userBillingAddress->save();
        
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
		$userShippingAddress = UserShippingAddress::getNewInstance($user, $address);
        $userShippingAddress->save();

        // set order addresses
        $order = CustomerOrder::getInstance();
        $order->billingAddress->set($userBillingAddress->userAddress->get());
        $order->shippingAddress->set($userShippingAddress->userAddress->get());
        $order->user->set($user);
        $order->save();
        
        ActiveRecordModel::commit();
        
        $user->setAsCurrentUser();       

        return new ActionRedirectResponse('checkout', 'confirmTotals');
    }
    
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

    /**
     *  Return a list of states for the selected country
     *  @return JSONResponse
     */
    public function states()
    {                
        return new JSONResponse(State::getStatesByCountry($this->request->getValue('country')));  
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
    	$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_err_enter_first_name')));
    	$validator->addCheck('lastName', new IsNotEmptyCheck($this->translate('_err_enter_last_name')));
    	$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_enter_email')));    
    	
        $emailErr = $this->translate('_err_not_unique_email');
        $emailErr = str_replace('%1', Router::getInstance()->createUrl(array('controller' => 'user', 'action' => 'login')), $emailErr);
        $validator->addCheck('email', new IsUniqueEmailCheck($emailErr));    
                    	
        if ($this->config->getValue('REQUIRE_PHONE'))
        {
            $validator->addCheck('phone', new IsNotEmptyCheck($this->translate('_err_enter_phone')));
        }

    	// validate billing info
        $validator->addCheck('billing_address1', new IsNotEmptyCheck($this->translate('_err_enter_address')));
        $validator->addCheck('billing_city', new IsNotEmptyCheck($this->translate('_err_enter_city')));
        $validator->addCheck('billing_country', new IsNotEmptyCheck($this->translate('_err_select_country')));
        $validator->addCheck('billing_zip', new IsNotEmptyCheck($this->translate('_err_enter_zip')));
                        
        $stateCheck = new OrCheck(array('billing_state_select', 'billing_state_text'), array(new IsNotEmptyCheck($this->translate('_err_select_state')), new IsNotEmptyCheck('')), $this->request);
        $validator->addCheck('billing_state_select', $stateCheck);
//        $validator->addCheck('billing_state_select', new IsValidStateCheck($this->translate('_err_select_state')));

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