<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.Currency');

/**
 *  Handles order checkout process
 *
 *  The order checkout consists of the following steps:
 *
 *  1. Determine user status
 *      
 *      If the user is logged in, this step is skipped
 *      If the user is not logged in there are 2 or 3 choices depending on configuration:
 *          a) log in
 *          b) create a new user account
 *          c) continue checkout without registration (anonymous checkout). 
 *             In this case the user account will be created automatically
 *
 *  2. Process login
 *  
 *      If the user is already logged in or is checking out anonymously this step is skipped.  
 *
 *  3. Select or enter billing and shipping addresses
 *      
 *      If the user has just been registered, this step is skipped, as these addresses have already been provided
 *      If the user was logged in, the billing and shipping addresses have to be selected (or new addresses entered/edited)
 *
 *  4. Select shipping method and calculate tax
 *
 *      Based on the shipping addresses, determine the available shipping methods and costs.
 *      Based on the shipping or billing address (depending on config), calculate taxes if any.
 *
 *  5. Confirm order totals and select payment method
 *
 *  6. Enter payment details
 *
 *      Redirected to external site if it's a 3rd party payment processor (like Paypal)
 *      This step is skipped if a non-online payment method is selected (check, wire transfer, phone, etc.)
 *
 *  7. Process payment and reserve products
 *      
 *      This step is skipped also if the payment wasn't made
 *      If the payment was attempted, but unsuccessful, return to payment form (6)
 *
 *  8. Process order and send invoice (optional)      
 *
 *      Whether the order is processed, depends on the configuration (auto vs manual processing)
 *  
 *  9. Show the order confirmation page
 *  
 *  
 */
class CheckoutController extends FrontendController
{
    const STEP_ADDRESS = 3;
    const STEP_SHIPPING = 4;
    const STEP_PAYMENT = 5;
    
    public function init()
    {
        parent::init();  
        $this->addBreadCrumb($this->translate('_checkout'), $this->router->createUrl(array('controller' => 'order', 'action' => 'index')));         
        
        $action = $this->request->getActionName();
                
        if ('index' == $action)
        {
            return false;
        }       

        $this->addBreadCrumb($this->translate('_select_addresses'), $this->router->createUrl(array('controller' => 'checkout', 'action' => 'selectAddress')));         		
		
    	if ('selectAddress' == $action)
    	{
			return false;	
		}
                
        $this->addBreadCrumb($this->translate('_shipping'), $this->router->createUrl(array('controller' => 'checkout', 'action' => 'shipping')));         		
		
    	if ('shipping' == $action)
    	{
			return false;	
		}

        $this->addBreadCrumb($this->translate('_pay'), $this->router->createUrl(array('controller' => 'checkout', 'action' => 'pay')));		
    }
    
    /**
     *  1. Determine user status
     */
    public function index()
    {
        $user = User::getCurrentUser();
        if ($user->isLoggedIn())
        {
            // try to go to payment page
            return new ActionRedirectResponse('checkout', 'pay');
        }    
        else
        {
            return new ActionRedirectResponse('user', 'checkout');
        }
    }
    
    /**
     *  3. Select or enter billing and shipping addresses
     *	@role login
     */
    public function selectAddress()
    {        
        $this->user->loadAddresses();
		
		// check if the user has created a billing address
        if (!$this->user->defaultBillingAddress->get())
        {
			return new ActionRedirectResponse('user', 'addBillingAddress', array('returnPath' => true));
		}
		
		$order = CustomerOrder::getInstance();
        
        if ($redirect = $this->validateOrder($order))
        {
			return $redirect;
		}
        
        $form = $this->buildAddressSelectorForm($order);
        
        if ($order->billingAddress->get())
        {
            $form->setValue('billingAddress', $order->billingAddress->get()->getID());
        }
        else
        {
            if ($this->user->defaultBillingAddress->get())
            {
				$form->setValue('billingAddress', $this->user->defaultBillingAddress->get()->userAddress->get()->getID());				
			}
        }
        
        if ($order->shippingAddress->get())
        {
            $form->setValue('shippingAddress', $order->shippingAddress->get()->getID());
        }
        else
        {
            if ($this->user->defaultShippingAddress->get())
            {
				$form->setValue('shippingAddress', $this->user->defaultShippingAddress->get()->userAddress->get()->getID());				
			}
        }
         
        $form->setValue('sameAsBilling', (int)($form->getValue('billingAddress') == $form->getValue('shippingAddress') || !$this->user->defaultShippingAddress->get()));
        
    	$response = new ActionResponse();
    	$response->setValue('billingAddresses', $this->user->getBillingAddressArray());
    	$response->setValue('shippingAddresses', $this->user->getShippingAddressArray());
    	$response->set('form', $form);
    	return $response;    	
    }

    /**
     *	@role login
     */           
    public function doSelectAddress()
    {
        $this->user->loadAddresses();
        
        $order = CustomerOrder::getInstance();        
        
        if (!$this->buildAddressSelectorValidator($order)->isValid())
        {
            return new ActionRedirectResponse('checkout', 'selectAddress');
        }   

        try
        {
            $f = new ARSelectFilter();
            $f->setCondition(new EqualsCond(new ARFieldHandle('BillingAddress', 'userID'), $this->user->getID()));
            $f->mergeCondition(new EqualsCond(new ARFieldHandle('BillingAddress', 'userAddressID'), $this->request->getValue('billingAddress')));
            $r = ActiveRecordModel::getRecordSet('BillingAddress', $f, array('UserAddress'));
            
            if (!$r->size())
            {
                throw new ApplicationException('Invalid billing address');
            }
            
            $billing = $r->get(0);
            $order->billingAddress->set($billing->userAddress->get());
                    
            // shipping address
            if ($order->isShippingRequired())
            {
                
                if ($this->request->getValue('sameAsBilling'))
                {
                    $shipping = $billing;
                }
                else
                {
    
                    $f = new ARSelectFilter();
                    $f->setCondition(new EqualsCond(new ARFieldHandle('ShippingAddress', 'userID'), $this->user->getID()));
                    $f->mergeCondition(new EqualsCond(new ARFieldHandle('ShippingAddress', 'userAddressID'), $this->request->getValue('shippingAddress')));
                    $r = ActiveRecordModel::getRecordSet('ShippingAddress', $f, array('UserAddress'));
                    
                    if (!$r->size())
                    {
                        throw new ApplicationException('Invalid shipping address');
                    }
    
                    $shipping = $r->get(0);
                }
                
                $order->shippingAddress->set($shipping->userAddress->get());            
            }
        }
        catch (Exception $e)
        {
            throw $e;
            return new ActionRedirectResponse('checkout', 'selectAddress');
        }
        
        $order->save();
		$order->syncToSession();
		
        return new ActionRedirectResponse('checkout', 'shipping');
    }
    
    /**
     *  4. Select shipping methods
     *	@role login
     */   
    public function shipping()
    {
        $order = CustomerOrder::getInstance();

        if ($redirect = $this->validateOrder($order, self::STEP_SHIPPING))
        {
			return $redirect;
		}
        
        if (!$order->isShippingRequired())
        {
            return new ActionRedirectResponse('checkout', 'pay');
        }
        
        $shipments = $order->getShipments();

        $form = $this->buildShippingForm($shipments);
        $zone = $order->getDeliveryZone();
        
        $needSelecting = false;
        
        foreach ($shipments as $key => $shipment)
        {
            $shipmentRates = $zone->getShippingRates($shipment);
            $shipment->setAvailableRates($shipmentRates);
                        
            if ($shipmentRates->size() > 1)
            {
                $needSelecting = true;
            }
            else if ($shipmentRates->size() == 1)
            {
                $shipment->setRateId($shipmentRates->get(0)->getServiceId());
            }
            
            $rates[$key] = $shipmentRates;
            if ($shipment->getSelectedRate())
            {
                $form->setValue('shipping_' . $key, $shipment->getSelectedRate()->getServiceID());                
            }
        }

        $order->syncToSession();
		$order->save();

        // only one shipping method for each shipment, so we pre-select it automatically
        if (!$needSelecting)
        {
            return new ActionRedirectResponse('checkout', 'pay');
        }

        $rateArray = array();
        foreach ($rates as $key => $rate)
        {
            $rateArray[$key] = $rate->toArray();
        }

        $response = new ActionResponse();
        $response->setValue('shipments', $shipments->toArray());
        $response->setValue('rates', $rateArray);
		$response->setValue('currency', $this->getRequestCurrency()); 
        $response->setValue('form', $form);
        return $response;
    }
    
    /**
     *	@role login
     */           
    public function doSelectShippingMethod()
    {
        $order = CustomerOrder::getInstance();
        $shipments = $order->getShipments();

        if (!$this->buildShippingValidator($shipments)->isValid())
        {
            return new ActionRedirectResponse('checkout', 'shipping');               
        }            

        foreach ($shipments as $key => $shipment)
        {
			if ($shipment->isShippable())
			{
                $rates = $shipment->getAvailableRates();
    			
    			$selectedRateId = $this->request->getValue('shipping_' . $key);
    			
                if (!$rates->getByServiceId($selectedRateId))
    			{
    				throw new ApplicationException('No rate found: ' . $key .' (' . $selectedRateId . ')');
    				return new ActionRedirectResponse('checkout', 'shipping');
    			}
    			
    			$shipment->setRateId($selectedRateId);
		    }
		}
        
        $order->saveToSession();
        
        return new ActionRedirectResponse('checkout', 'pay');
    }
    
    /**
     *  5. Make payment
     *	@role login
     */   
    public function pay()
    {
        $order = CustomerOrder::getInstance();    
        $order->loadAll();	
        
        if ($redirect = $this->validateOrder($order, self::STEP_PAYMENT))
        {
			return $redirect;
		}       
        
        $currency = $this->request->getValue('currency', $this->store->getDefaultCurrencyCode());
                
        $response = new ActionResponse();
        $response->setValue('order', $order->toArray());
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode())); 
        
        $ccHandler = $this->store->getCreditCardHandler();
        if ($ccHandler)
        {
			$response->setValue('ccHandler', $ccHandler->toArray());
			$response->setValue('ccForm', $this->buildCreditCardForm());
			
			$months = range(1, 12);
			$months = array_combine($months, $months);
			
			$years = range(date('Y'), date('Y') + 20);
			$years = array_combine($years, $years);
			
			$response->setValue('months', $months);
			$response->setValue('years', $years);
            $response->set('ccTypes', $this->store->getCardTypes($ccHandler));
		}
		
        return $response;                        
    }

    /**
     *	@role login
     */       
    public function payCreditCard()
	{
        ClassLoader::import('application.model.order.*');        
        $order = CustomerOrder::getInstance();		

        if ($redirect = $this->validateOrder($order, self::STEP_PAYMENT))
        {
			return $redirect;
		}

		if (!$this->buildCreditCardValidator()->isValid())
		{
            return new ActionRedirectResponse('checkout', 'pay');
        }
        
        // already paid?
        if ($order->isPaid->get())
        {
            return new ActionRedirectResponse('checkout', 'completed');
        }
        
        ActiveRecordModel::beginTransaction();
        
        $currency = Currency::getValidInstanceById($this->getRequestCurrency());
        
        // set up transaction details
        $transaction = new LiveCartTransaction($order, $currency);
        
        // process payment
        $handler = $this->store->getCreditCardHandler($transaction);
        if ($this->request->isValueSet('ccType'))
        {
            $handler->setCardType($this->request->getValue('ccType'));
        }
        		
        $handler->setCardData($this->request->getValue('ccNum'), $this->request->getValue('ccExpiryMonth'), $this->request->getValue('ccExpiryYear'), $this->request->getValue('ccCVV'));
        
        if ($this->config->getValue('CC_AUTHONLY'))
        {
            $result = $handler->authorize();
        }
        else
        {
            $result = $handler->authorizeAndCapture();
        }
        
        if ($result instanceof TransactionResult)
        {
            $order->isPaid->set(true);
            $newOrder = $order->finalize($currency);
			            
            $this->session->setValue('completedOrderID', $order->getID());          
            
            $transaction = Transaction::getNewInstance($order, $result);
            $transaction->setHandler($handler);
            $transaction->save();
            
            $this->session->unsetValue('CustomerOrder');
            $newOrder->saveToSession();

            $response = new ActionRedirectResponse('checkout', 'completed');
        }
        elseif ($result instanceof TransactionError)
        {
            $validator = $this->buildCreditCardValidator();
//            var_dump($handler, $result, $result->getDetails()->Errors, $handler->getDetails()->getData()); exit;
            // set error message for credit card form
            $validator->triggerError('creditCardError', $this->translate('_err_processing_cc'));
            $validator->saveState();
            
            $response = new ActionRedirectResponse('checkout', 'pay');
        }
        else
        {
            throw new Exception('Unknown transaction result type: ' . get_class($result));
        }
        
        ActiveRecordModel::commit();        
        
        return $response;
	}
	
    /**
     *	@role login
     */       
	public function completed()
	{
        $order = CustomerOrder::getInstanceByID((int)$this->session->getValue('completedOrderID'));
        
        $response = new ActionResponse();
        $response->setValue('order', $order->toArray());    
        $response->setValue('url', $this->router->createUrl(array('controller' => 'user')));
        return $response;        
    }
    
    public function cvv()
    {
        $this->addBreadCrumb($this->translate('_cvv'), '');         		

		return new ActionResponse();
	}
    
    /******************************* VALIDATION **********************************/
    
    /**
     *	Determines if the necessary steps have been completed, so the order could be finalized
     *
     *	@return RedirectResponse
     *	@return ActionRedirectResponse
     *	@return false
	 */
	private function validateOrder(CustomerOrder $order, $step = 0)
    {
		// no items in shopping cart
		if (!count($order->getShoppingCartItems()))
		{
			if ($this->request->isValueSet('return'))
			{
				return new RedirectResponse($this->router->createUrlFromRoute($this->request->getValue('return')));
			}		
			else
			{
				return new ActionRedirectResponse('index', 'index');
			}
		}
		
        // shipping address selected
        if ($step >= self::STEP_SHIPPING)
        {            
            if ((!$order->shippingAddress->get() && $order->isShippingRequired()) || !$order->billingAddress->get())
            {
                return new ActionRedirectResponse('checkout', 'selectAddress');
            }            
        }
        
        // shipping method selected
        if ($step >= self::STEP_PAYMENT && $order->isShippingRequired())
        {            
            foreach ($order->getShipments() as $shipment)
            {
                if (!$shipment->getSelectedRate() && $shipment->isShippable())
                {
                    return new ActionRedirectResponse('checkout', 'shipping');
                }
            }
        }
		
		return false;		
	}
    
    private function buildShippingForm(/*ARSet */$shipments)
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildShippingValidator($shipments));        
    }

    private function buildShippingValidator(/*ARSet */$shipments)
    {
		ClassLoader::import("framework.request.validator.RequestValidator");        
        $validator = new RequestValidator("shipping", $this->request);
        foreach ($shipments as $key => $shipment)		
        {
            if ($shipment->isShippable())
            {
                $validator->addCheck('shipping_' . $key, new IsNotEmptyCheck($this->translate('_err_select_shipping')));
            }
        }
        return $validator;
    }

    private function buildAddressSelectorForm(CustomerOrder $order)
    {
		ClassLoader::import("framework.request.validator.Form");
        $validator = new RequestValidator("addressSelectorValidator_blank", $this->request);
		return new Form($validator);        
    }
    
    private function buildAddressSelectorValidator(CustomerOrder $order)
    {
		ClassLoader::import("framework.request.validator.Form");
        $validator = new RequestValidator("addressSelectorValidator", $this->request);
        $validator->addCheck('billingAddress', new IsNotEmptyCheck($this->translate('_select_billing_address')));
        
        if ($order->isShippingRequired())
        {
            $validator->addCheck('shippingAddress', new OrCheck(array('shippingAddress', 'sameAsBilling'), array(new IsNotEmptyCheck($this->translate('_select_shipping_address')), new IsNotEmptyCheck('')), $this->request));            
        }
        
        return $validator;
    }

    private function buildCreditCardForm()
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildCreditCardValidator());        
    }

    private function buildCreditCardValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");        
        $validator = new RequestValidator("creditCard", $this->request);
        $validator->addCheck('ccNum', new IsNotEmptyCheck($this->translate('_err_enter_cc_num')));
//        $validator->addCheck('ccType', new IsNotEmptyCheck($this->translate('_err_select_cc_type')));
        $validator->addCheck('ccExpiryMonth', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_month')));
        $validator->addCheck('ccExpiryYear', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_year')));
        
		if ($this->config->getValue('REQUIRE_CVV'))
		{
			$validator->addCheck('ccCVV', new IsNotEmptyCheck($this->translate('_err_enter_cc_cvv')));
		}
       
    	$validator->addFilter('ccCVV', new RegexFilter('[^0-9]'));
    	$validator->addFilter('ccNum', new RegexFilter('[^ 0-9]'));
       
        return $validator;
    }
}
    
?>