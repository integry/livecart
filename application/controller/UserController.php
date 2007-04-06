<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.delivery.State');

/**
 *  Handles user account related logic
 */
class UserController extends FrontendController
{
    public function register()
    {
        $defCountry = $this->config->getValue('DEF_COUNTRY');
        
        $form = $this->buildForm();
        $form->setValue('billing_country', $defCountry);

        $countries = $this->locale->info()->getAllCountries();
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
    
    public function processRegistration()
    {
        $validator = $this->buildValidator();
        if (!$validator->isValid())
        {
            return new ActionRedirectResponse('user', 'register');
        }

        // create user account
        $user = User::getNewInstance($this->request->getValue('email'), $this->request->getValue('password'));
        $user->name->set($this->request->getValue('name'));
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
        $billingAddress = UserBillingAddress::getNewInstance($user);
        $billingAddress->name->set($user->name->get());
        $billingAddress->address1->set($this->request->getValue('billing_address1'));        
        $billingAddress->address2->set($this->request->getValue('billing_address2'));        
                
        $this->loginUser($user);       
        return new RedirectResponse($this->request->getValue('return'));
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
    	$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_enter_name')));
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