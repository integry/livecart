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
    public function init()
    {
        parent::init();  
        $router = Router::getInstance();
        $this->addBreadCrumb($this->translate('_checkout'), $router->createUrl(array('controller' => 'order', 'action' => 'index')));         
        
        $action = $this->request->getActionName();
                
        if ('index' == $action)
        {
            return false;
        }       

        $this->addBreadCrumb($this->translate('_select_addresses'), $router->createUrl(array('controller' => 'checkout', 'action' => 'selectAddress')));         		
		
    	if ('selectAddress' == $action)
    	{
			return false;	
		}
                
        $this->addBreadCrumb($this->translate('_shipping'), $router->createUrl(array('controller' => 'checkout', 'action' => 'shipping')));         		
		
    	if ('shipping' == $action)
    	{
			return false;	
		}

        $this->addBreadCrumb($this->translate('_pay'), $router->createUrl(array('controller' => 'checkout', 'action' => 'pay')));         		
		
    }
    
    /**
     *  1. Determine user status
     */
    public function index()
    {
        $user = User::getCurrentUser();
        if ($user->isLoggedIn())
        {
            // go to step 3
            return new ActionRedirectResponse('checkout', 'shipping');
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
        $order = CustomerOrder::getInstance();
        
        $form = $this->buildAddressSelectorForm();
        
        if ($order->billingAddress->get()->getID())
        {
            $form->setValue('billingAddress', $order->billingAddress->get()->getID());
        }
        else
        {
            $form->setValue('billingAddress', $this->user->defaultBillingAddress->get()->userAddress->get()->getID());
        }
        
        if ($order->shippingAddress->get()->getID())
        {
            $form->setValue('shippingAddress', $order->shippingAddress->get()->getID());
        }
        else
        {
            $form->setValue('shippingAddress', $this->user->defaultShippingAddress->get()->userAddress->get()->getID());
        }
         
        $form->setValue('sameAsBilling', (int)($form->getValue('billingAddress') == $form->getValue('shippingAddress')));
        
    	$response = new ActionResponse();
    	$response->setValue('billingAddresses', $this->user->getBillingAddressArray());
    	$response->setValue('shippingAddresses', $this->user->getShippingAddressArray());
    	$response->set('form', $form);
    	return $response;    	
    }
    
    public function doSelectAddress()
    {
        if (!$this->buildAddressSelectorValidator()->isValid())
        {
            return new ActionRedirectResponse('checkout', 'selectAddress', array('id' => 1));
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
        }
        catch (Exception $e)
        {
            return new ActionRedirectResponse('checkout', 'selectAddress', array('id' => 2, 'query' => 'msg=' . $e->getMessage()));               
        }
        
        $order = CustomerOrder::getInstance();
        $order->shippingAddress->set($shipping->userAddress->get());
        $order->billingAddress->set($billing->userAddress->get());
        $order->save();
        
        return new ActionRedirectResponse('checkout', 'shipping');
    }
    
    public function shipping()
    {
        // get shipping address
        $order = CustomerOrder::getInstance();
        $address = $order->shippingAddress->get();
        if (!$address)
        {
            return new ActionRedirectResponse('checkout', 'selectAddress');
        }
        
        $shipments = $order->getShipments();
        $zone = $order->getDeliveryZone();
        foreach ($shipments as $key => $shipment)
        {
            $shipmentRates = $zone->getShippingRates($shipment);
            $shipment->setAvailableRates($shipmentRates);
            $rates[$key] = $shipmentRates;
        }

        $order->syncToSession();
		$order->save();

        $rateArray = array();
        foreach ($rates as $key => $rate)
        {
            $rateArray[$key] = $rate->toArray();
        }

        $response = new ActionResponse();
        $response->setValue('shipments', $shipments->toArray());
        $response->setValue('rates', $rateArray);
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode())); 
        $response->setValue('form', $this->buildShippingForm($shipments));
        return $response;
    }
    
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
			$rates = $shipment->getAvailableRates();
			
			$selectedRateId = $this->request->getValue('shipping_' . $key);
			
            if (!$rates->getByServiceId($selectedRateId))
			{
//				throw new ApplicationException('No rate found: ' . $key .' (' . $selectedRateId . ')');
				return new ActionRedirectResponse('checkout', 'shipping');
			}
			
			$shipment->setRateId($selectedRateId);
		}
        
        $order->syncToSession();
        
        return new ActionRedirectResponse('checkout', 'pay');
    }
    
    public function pay()
    {
        $order = CustomerOrder::getInstance();       
        
        if (!$order->isShippingSelected())
        {
            return new ActionRedirectResponse('checkout', 'shipping');
        }
        
        $currency = $this->request->getValue('currency', $this->store->getDefaultCurrencyCode());
                
        $response = new ActionResponse();
        $response->setValue('order', $order->toArray());
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode())); 
        
        $ccHandler = Store::getInstance()->getCreditCardHandler();
        if ($ccHandler)
        {
			$response->setValue('ccHandler', $ccHandler->toArray());
			$response->setValue('ccForm', $this->buildCreditCardForm());
			
			$months = range(1, 12);
			$years = range(date('Y'), date('Y') + 20);
			$response->setValue('months', $months);
			$response->setValue('years', $years);
		}
		
        return $response;                        
    }
    
    private function buildShippingForm(ARSet $shipments)
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildShippingValidator($shipments));        
    }

    private function buildShippingValidator(ARSet $shipments)
    {
		ClassLoader::import("framework.request.validator.RequestValidator");        
        $validator = new RequestValidator("shipping", $this->request);
        foreach ($shipments as $key => $shipment)		
        {
            $validator->addCheck('shipping_' . $key, new IsNotEmptyCheck($this->translate('_err_select_shipping')));
        }
        return $validator;
    }

    private function buildAddressSelectorForm()
    {
		ClassLoader::import("framework.request.validator.Form");
        $validator = new RequestValidator("addressSelectorValidator_blank", $this->request);
		return new Form($validator);        
    }
    
    private function buildAddressSelectorValidator()
    {
		ClassLoader::import("framework.request.validator.Form");
        $validator = new RequestValidator("addressSelectorValidator", $this->request);
        $validator->addCheck('billingAddress', new IsNotEmptyCheck($this->translate('_select_billing_address')));
        $validator->addCheck('shippingAddress', new OrCheck(array('shippingAddress', 'sameAsBilling'), array(new IsNotEmptyCheck($this->translate('_select_shipping_address')), new IsNotEmptyCheck('')), $this->request));
        
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
        $validator = new RequestValidator("creditCart", $this->request);
        $validator->addCheck('ccNum', new IsNotEmptyCheck($this->translate('_err_enter_cc_num')));
        $validator->addCheck('ccType', new IsNotEmptyCheck($this->translate('_err_select_cc_type')));
        $validator->addCheck('ccExpiryMonth', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_month')));
        $validator->addCheck('ccExpiryYear', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_year')));
        $validator->addCheck('ccCVV', new IsNotEmptyCheck($this->translate('_err_select_cc_type')));
       
        return $validator;
    }
}
    
?>