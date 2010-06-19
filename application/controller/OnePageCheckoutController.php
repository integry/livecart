<?php

ClassLoader::import('application.controller.CheckoutController');
ClassLoader::import('application.controller.UserController');

/**
 *  Handles order checkout process in one page
 *
 * @author Integry Systems
 * @package application.controller
 */
class OnePageCheckoutController extends CheckoutController
{
	public function init()
	{
		parent::init();
		$this->loadLanguageFile('Order');
		$this->loadLanguageFile('User');
	}
	
	public function index()
	{
		$response = new CompositeActionResponse();
		
		$blocks = array('login', 'shippingAddress', 'billingAddress', 'shippingMethods');
		foreach ($blocks as $block)
		{
			$blockResponse = $this->$block();
			if ($blockResponse)
			{
				$blockResponse->set('user', $this->user->toArray());
				$response->addResponse($block, $blockResponse, $this, $block);
			}
		}
		
		return $response;
	}

	public function login()
	{
		if ($this->user->isAnonymous())
		{
			return;
		}

		return new ActionResponse();
	}
	
	public function shippingAddress()
	{
		if ($this->user->isAnonymous())
		{
			$response = $this->getUserController()->checkout();
		}
		else
		{
			$this->request->set('step', 'shipping');
			$this->config->set('ENABLE_CHECKOUTDELIVERYSTEP', true);
			$this->config->set('DISABLE_CHECKOUT_ADDRESS_STEP', false);
			
			$response = parent::selectAddress();
		}
		
		return $response;
	}

	public function billingAddress()
	{
		if (!$this->order->isShippingRequired())
		{
			return null;
		}
		
		$this->request->set('step', 'billing');
		$this->config->set('ENABLE_CHECKOUTDELIVERYSTEP', true);
		$this->config->set('DISABLE_CHECKOUT_ADDRESS_STEP', false);

		$response = parent::selectAddress();
		
		return $response;
	}
	
	public function shippingMethods()
	{
		$response = $this->shipping();
		
		//var_dump(array_keys($response->getData()));
		
		return $response;
	}
	
	public function doLogin()
	{
		
	}
	
	protected function getUserController()
	{
		return new UserController($this->application);
	}
	
	/**
	 *  Overrides for parent controller
	 */
	protected function validateOrder(CustomerOrder $order, $step = 0)
	{
		
	}
}

?>
