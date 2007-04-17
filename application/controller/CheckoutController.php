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
            return new ActionRedirectResponse('checkout', 'selectAddress');
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
        $this->addBreadCrumb($this->translate('_select_addresses'), '');
        
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
        
        return new ActionRedirectResponse('checkout', 'confirmTotals');
    }
    
    public function confirmTotals()
    {
        // get shipping address
        $order = CustomerOrder::getInstance();
        $address = $order->shippingAddress->get();
        if (!$address)
        {
            return new ActionRedirectResponse('checkout', 'selectAddress');
        }
        
        $rates = $order->getShippingRates();
        
        print_r($rates);
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
}
    
?>