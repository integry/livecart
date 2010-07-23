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
	protected $ignoreValidation = false;
	protected $isTosRequired = false;

	const CUSTOM_FIELDS_STEP = 'SHIPPING_ADDRESS_STEP';

	public function init()
	{
		if ($this->config->get('CHECKOUT_METHOD') == 'CHECKOUT_MULTISTEP')
		{
			return new ActionRedirectResponse('checkout', 'index');
		}

		$this->isTosRequired = $this->config->get('REQUIRE_TOS');

		parent::init();
		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', self::CUSTOM_FIELDS_STEP);
		$this->config->setRuntime('ENABLE_CHECKOUTDELIVERYSTEP', true);
		$this->config->setRuntime('DISABLE_CHECKOUT_ADDRESS_STEP', false);
		$this->config->setRuntime('ENABLE_SHIPPING_ESTIMATE', false);
		$this->config->setRuntime('SKIP_SHIPPING', false);
		$this->config->setRuntime('SKIP_PAYMENT', false);
		$this->config->setRuntime('REG_EMAIL_CONFIRM', false);
		$this->config->setRuntime('EMAIL_NEW_USER', false);
		$this->config->setRuntime('REQUIRE_TOS', false);

		$this->loadLanguageFile('Order');
		$this->loadLanguageFile('User');

		$this->order->loadSpecification();
		ActiveRecordModel::loadEav();

		if ($this->user->isAnonymous())
		{
			if ($this->session->get('checkoutUser'))
			{
				$checkoutUser = unserialize($this->session->get('checkoutUser'));
				$checkoutUser->setID(null);
				SessionUser::setUser($checkoutUser);

				if ($this->user !== $checkoutUser && ($checkoutUser instanceof User))
				{
					$this->user = $checkoutUser;
				}
			}

			$this->user->grantAccess('login');

			if ($this->user->defaultShippingAddress->get() && ($this->isShippingRequired($this->order) || $this->config->get('REQUIRE_SAME_ADDRESS')))
			{
				$address = $this->user->defaultShippingAddress->get()->userAddress->get();
				$this->order->shippingAddress->set($address);
				$this->order->billingAddress->set($address);
			}

			if ($this->user->defaultBillingAddress->get())
			{
				$address = $this->user->defaultBillingAddress->get()->userAddress->get();

				if ($address)
				{
					$this->order->billingAddress->set($address);
					if (!$this->order->shippingAddress->get() && $this->isShippingRequired($this->order))
					{
						$this->order->shippingAddress->set($address);
					}
				}
			}

			$this->order->user->set($this->user);
			$this->order->shippingAddress->resetModifiedStatus();
			$this->order->billingAddress->resetModifiedStatus();
			$this->order->user->resetModifiedStatus();
		}
		else
		{
			$this->user->load();
			$this->user->loadAddresses();

			$address = $this->user->defaultShippingAddress->get();
			if (!$address)
			{
				$address = $this->user->defaultBillingAddress->get();
			}

			if (!$this->order->shippingAddress->get() && $address && $this->isShippingRequired($this->order))
			{
				$userAddress = $address->userAddress->get();
				$this->order->shippingAddress->set($userAddress);
			}

			$address = $this->user->defaultBillingAddress->get();
			if (!$this->order->billingAddress->get() && $address)
			{
				$userAddress = $address->userAddress->get();
				$this->order->billingAddress->set($userAddress);
			}

			$this->order->save();
		}
	}

	public function index()
	{
		$this->order->loadAll();

		if (!$this->order->getShoppingCartItemCount())
		{
			return new ActionRedirectResponse('index', 'index');
		}

		$this->session->set('OrderPaymentMethod_' . $this->order->getID(), '');

		$response = new CompositeActionResponse();

		$blocks = array('login', 'shippingAddress', 'billingAddress', 'payment', 'cart', 'shippingMethods', 'overview');
		$blocks = array_flip($blocks);

		if ($this->config->get('REQUIRE_SAME_ADDRESS'))
		{
			unset($blocks['billingAddress']);
		}

		foreach ($blocks as $block => $key)
		{
			$blockResponse = $this->$block();

			if ($blockResponse instanceof ActionResponse)
			{
				$blockResponse->set('user', $this->user->toArray());
				$response->addResponse($block, $blockResponse, $this, $block);
			}
		}

		$response->set('orderValues', $this->getOrderValues($this->order));

		return $response;
	}

	public function login()
	{
		if (!$this->user->isAnonymous())
		{
			return;
		}

		$response = new ActionResponse();
		if ($this->request->get('failedLogin'))
		{
			$response->set('failedLogin', true);
		}
		return $response;
	}

	public function shippingAddress()
	{
		$sameAddress = $this->config->get('REQUIRE_SAME_ADDRESS');
		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', false);

		if ($this->user->isAnonymous())
		{
			$response = $this->getUserController()->checkout();
			$form = $response->get('form');

			if ($this->user->defaultShippingAddress->get())
			{
				$addressInstance = $this->user->defaultShippingAddress->get()->userAddress->get();
				$shippingAddress = $addressInstance->toFlatArray();

				foreach ($shippingAddress as $key => $value)
				{
					$form->set('shipping_' . $key, $value);
				}

				$form->set('shipping_state_select', $shippingAddress['State']);
				$form->set('shipping_country', $shippingAddress['countryID']);

				if ($spec = $addressInstance->getSpecification())
				{
					$form->setData($spec->getFormData('shipping_'));
				}
			}

			$form->setData($this->user->toFlatArray());
			if ($spec = $this->user->getSpecification())
			{
				$form->setData($spec->getFormData());
			}

			$form->set('password', '');

			$this->order->getSpecification()->setFormResponse($response, $form, 'order_');
		}
		else
		{
			/*
			if ($this->order->shippingAddress->get() && !$this->user->defaultShippingAddress->get())
			{
				$this->user->defaultShippingAddress->set(ShippingAddress::getNewInstance(clone $this->order->shippingAddress->get(), $this->user));
				$this->user->defaultShippingAddress->get()->save();
			}
			*/

			$this->request->set('step', 'shipping');
			$response = parent::selectAddress();

			if (!$response instanceof ActionResponse)
			{
				return null;
			}

			$response->set('step', 'shipping');

			//if (!$this->order->isShippingRequired())
			//{
				$form = $response->get('form');
				$formData = $this->switchArrayPrefixes($form->getData(), 'billing_', 'shipping_');
				$form->setData($formData);
			//}
		}

		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', $sameAddress);

		return $this->postProcessResponse($response);
	}

	public function billingAddress()
	{
		if (!$this->isShippingRequired($this->order))
		{
			return null;
		}

		$this->request->set('step', 'billing');

		$response = $this->postProcessResponse(parent::selectAddress());

		$bill = $this->order->billingAddress->get();
		$ship = $this->order->shippingAddress->get();
		if (!$bill || ($bill && $ship && (($bill->getID() == $ship->getID()) || ($bill->toString() == $ship->toString()))))
		{
			$response->get('form')->set('sameAsShipping', true);
		}

		return $response;
	}

	public function shippingMethods()
	{
		// shipping methods won't be displayed if custom fields are not filled
		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', 'SHIPPING_METHOD_STEP');

		$tempShipping = false;
		if (!$this->order->shippingAddress->get())
		{
			$this->order->shippingAddress->set(SessionOrder::getEstimateAddress());
			$tempShipping = true;
		}

		$tempBilling = false;
		if (!$this->order->billingAddress->get())
		{
			$this->order->billingAddress->set($this->order->shippingAddress->get());
			$tempBilling = true;
		}

		$response = $this->shipping();
		$this->order->serializeShipments();

		if ($tempShipping)
		{
			$this->order->shippingAddress->setNull();
		}

		if ($tempBilling)
		{
			$this->order->billingAddress->setNull();
		}

		$this->order->save();

		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', self::CUSTOM_FIELDS_STEP);

		return $this->postProcessResponse($response);
	}

	public function cart()
	{
		return $this->postProcessResponse($this->getOrderController()->index());
	}

	public function overview()
	{
		if (!$this->order->isShippingRequired())
		{
			$this->order->shippingAddress->setNull();
		}

		$this->order->resetArrayData();
		$array = $this->order->toArray();

		if ($this->config->get('REQUIRE_SAME_ADDRESS'))
		{
			$array['BillingAddress'] = $array['ShippingAddress'];
		}

		return $this->postProcessResponse(new ActionResponse('order', $array));
	}

	public function payment()
	{
		if ($this->config->get('REQUIRE_SAME_ADDRESS'))
		{
			$this->order->billingAddress->set($this->order->shippingAddress->get());
			$this->order->billingAddress->resetModifiedStatus();
		}

		$this->ignoreValidation = true;
		$response = $this->postProcessResponse($this->pay());
		$this->ignoreValidation = false;

		$paymentMethodForm = new Form($this->getPaymentMethodValidator());
		if ($this->isTosRequired)
		{
			$paymentMethodForm->set('tos', $this->session->get('tos'));
		}

		$response->set('form', $paymentMethodForm);
		$response->set('requireTos', $this->isTosRequired);
		return $response;
	}

	protected function getPaymentMethodValidator()
	{
		$validator = $this->getValidator('setPaymentMethod');
		if ($this->isTosRequired)
		{
			$validator->addCheck('tos', new IsNotEmptyCheck($this->translate('_err_agree_to_tos')));
		}

		return $validator;
	}

	public function doLogin()
	{
		$res = $this->getUserController()->doLogin();
		$params = array();
		if ($res->getActionName() == 'login')
		{
			$params = array('query' => 'failedLogin=true');
		}

		return new ActionRedirectResponse('onePageCheckout', 'index', $params);
	}

	public function doSelectShippingMethod()
	{
		//$this->order->loadAll();

		parent::doSelectShippingMethod();

		$this->order->getTotal(true);

		return $this->getUpdateResponse();
	}

	public function doSelectShippingAddress()
	{
		$sameAddress = $this->config->get('REQUIRE_SAME_ADDRESS');
		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', false);

		$this->order->loadAll();

		$this->request->set('step', 'shipping');

		$this->initAnonUser();

		if ($this->user->isAnonymous())
		{
			$billingAddress = $this->order->billingAddress->get();

			$this->request->set('sameAsBilling', true);
			foreach ($this->switchArrayPrefixes($this->request->toArray(), 'shipping_', 'billing_') as $key => $value)
			{
				$this->request->set($key, $value);
			}

			$controller = $this->getUserController();
			$response = $controller->processCheckoutRegistration();

			$user = $controller->getUser();
			if ($billingAddress && !$this->request->get('sameAsShipping'))
			{
				$this->order->billingAddress->set($billingAddress);
				$this->order->billingAddress->resetModifiedStatus();
				$user->defaultBillingAddress->get()->userAddress->set($billingAddress);
			}

			ActiveRecordModel::rollback();

			$this->order->getSpecification()->loadRequestData($this->request, 'order_');
			$this->order->getSpecification()->save();

			if (!$this->order->eavObject->get()->getID())
			{
				$this->order->eavObject->get()->save();
			}

			$this->order->eavObject->setAsModified(true);

			$this->order->save();

			ActiveRecordModel::commit();

			if ($response->getActionName() == 'checkout')
			{
				$errorResponse = $controller->checkout();
				$errors = $errorResponse->get('form')->getValidator()->getErrorList();
				return new JSONResponse(array('errorList' => $this->switchArrayPrefixes($errors, 'billing_', 'shipping_')));
			}

			$this->saveAnonUser($user);
		}
		else
		{
			$this->separateBillingAndShippingAddresses();
			$parentResponse = parent::doSelectAddress();

			if ($this->request->get('sameAsShipping') && $this->order->billingAddress->get() && $this->order->shippingAddress->get() && ($this->order->billingAddress->get()->toString() != $this->order->shippingAddress->get()->toString()))
			{
				$this->order->billingAddress->set(clone $this->order->shippingAddress->get());
				$this->order->billingAddress->get()->save();
				$this->order->save();
			}

			if ($sameAddress)
			{
				$this->order->billingAddress->set($this->order->shippingAddress->get());
			}
		}

		// attempt to pre-select a shipping method
		$this->shippingMethods();

		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', $sameAddress);

		return $this->getUpdateResponse('shippingMethods', 'billingAddress');
	}

	private function separateBillingAndShippingAddresses()
	{
		if (!$this->request->get('sameAsShipping') && $this->order->shippingAddress->get() && $this->order->billingAddress->get() && ($this->order->shippingAddress->get()->getID() == $this->order->billingAddress->get()->getID()) && !$this->user->defaultShippingAddress->get())
		{
			$this->user->defaultShippingAddress->set(ShippingAddress::getNewInstance($this->user, clone $this->order->shippingAddress->get()));
			$this->user->defaultShippingAddress->get()->save();
		}
	}

	private function switchArrayPrefixes($array, $from, $to)
	{
		foreach ($array as $key => $value)
		{
			if (substr($key, 0, strlen($from)) == $from)
			{
				$array[$to . substr($key, strlen($from))] = $value;
				unset($array[$key]);
			}
		}

		return $array;
	}

	public function doSelectBillingAddress()
	{
		$this->order->loadAll();
		$this->request->set('step', 'billing');
		$this->initAnonUser();

		$shipments = $this->order->getShipments();

		if (!$this->user->isAnonymous())
		{
			$this->separateBillingAndShippingAddresses();
		}

		if (!$this->request->get('sameAsShipping') && $this->order->shippingAddress->get() && $this->order->billingAddress->get() && ($this->order->billingAddress->get()->getID() == $this->order->shippingAddress->get()->getID()))
		{
			$this->order->billingAddress->set(clone $this->order->billingAddress->get());
			$this->order->billingAddress->get()->save();
		}

		if ($this->request->get('sameAsShipping'))
		{
			$this->order->billingAddress->set(clone $this->order->shippingAddress->get());
			$this->order->billingAddress->get()->save();
			if (!$this->user->isAnonymous())
			{
				$this->order->save();
			}
		}
		else
		{
			try
			{
				$res = parent::doSelectAddress();

				if ($res instanceof ActionRedirectResponse && ($res->getActionName() == 'selectAddress'))
				{
					$params = $res->getParamList();
					if (empty($params['step']) || ('shipping' != $params['step']))
					{
						$errorResponse = parent::selectAddress();
						$errorList = $errorResponse->get('form')->getValidator()->getErrorList();

						if ($errorList)
						{
							return new JSONResponse(array('errorList' => $errorList));
						}
					}
				}
			}

			// billing address ID is not in DB
			catch (SQLException $e)
			{
				//throw $e;
				$this->order->billingAddressID->resetModifiedStatus();
				SessionOrder::save($this->order);
			}
		}

		if ($this->user->isAnonymous())
		{
			$this->user->defaultBillingAddress->get()->userAddress->set($this->order->billingAddress->get());
			$this->saveAnonUser($this->user);
		}

		// attempt to pre-select a shipping method
		$this->shippingMethods();

		return $this->getUpdateResponse('shippingMethods');
	}

	public function updateCart()
	{
		$response = $this->getOrderController()->update();
		$this->shipping();

		if ($this->isAjax())
		{
			$response = $this->getUpdateResponse('shippingMethods');
		}
		else
		{
			return new ActionRedirectResponse('onePageCheckout', 'index');
		}

		return $response;
	}

	public function fallback()
	{
		$this->session->set('noJS', true);
		return new ActionRedirectResponse('checkout', 'index');
	}

	public function setPaymentMethod()
	{
		$this->order->loadAll();

		// @todo: in case only one shipping rate is available, it is unselected when setting payment method unless the shipping() method is called
		$this->shipping();

		$method = $this->request->get('payMethod');
		if ('cc' == $method)
		{
			$method = $this->config->get('CC_HANDLER');
		}

		$this->session->set('OrderPaymentMethod_' . $this->order->getID(), $method);
		$this->order->setPaymentMethod($method);

		$this->order->getTotal(true);

		return $this->getUpdateResponse();
	}

	public function payCreditCard()
	{
		$this->beforePayment();
		return parent::payCreditCard();
	}

	public function redirect()
	{
		$this->beforePayment();
		return parent::redirect();
	}

	public function payOffline()
	{
		$this->beforePayment();
		return parent::payOffline();
	}

	protected function beforePayment()
	{
		$this->registerAnonUser();

		if (!$this->order->isShippingRequired())
		{
			$this->order->shippingAddress->setNull();
		}
	}

	protected function getUserController()
	{
		return new UserController($this->application);
	}

	protected function getOrderController()
	{
		return new OrderController($this->application);
	}

	/**
	 *  $isCompleted = false => checks whether the particular checkout steps are available for modification
	 *  $isCompleted = true => tests if the steps have already been completed
	 */
	protected function getStepStatus(CustomerOrder $order, $isCompleted = 0)
	{
		$isStepEditable = $isCompleted;

		// validation will return false for all steps if custom fields are not filled
		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', 'SHIPPING_METHOD_STEP');

		$res = array('shippingAddress' => !$this->validateOrder($order, self::STEP_ADDRESS + $isStepEditable),
					 'shippingMethod' => !$this->validateOrder($order, self::STEP_SHIPPING + $isStepEditable),
					 'billingAddress' => !$this->validateOrder($order, self::STEP_ADDRESS + $isStepEditable),
					 'payment' => !$this->validateOrder($order, self::STEP_PAYMENT));

		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', self::CUSTOM_FIELDS_STEP);

		if (!$order->shippingAddress->get() && $this->order->isShippingRequired())
		{
			if ($isCompleted)
			{
				$res['shippingAddress'] = false;
			}

			$res['billingAddress'] = false;
		}

		if (!$order->billingAddress->get())
		{
			if ($isCompleted)
			{
				$res['billingAddress'] = false;
			}

			$res['payment'] = false;
		}

		if ($isCompleted)
		{
			$res['payment'] = false;
		}

		if (!$isCompleted)
		{
			$res['shippingMethod'] = true;
		}

		return $res;
	}

	protected function checkAccess()
	{
		return true;
	}

	protected function validateOrder(CustomerOrder $order, $step = 0)
	{
		return !$this->ignoreValidation ? parent::validateOrder($order, $step) : false;
	}

	protected function getOrderValues(CustomerOrder $order)
	{
		$orderArray = array('total' => $order->getTotal());
		$currID = $order->getCurrency()->getID();
		$orderArray['currencyID'] = $currID;
		$orderArray['formattedTotal'] = $order->getCurrency()->getFormattedPrice($orderArray['total']);
		$orderArray['basketCount'] = $order->getShoppingCartItemCount();

		$isOrderable = $order->isOrderable();
		$orderArray['isOrderable'] = is_bool($isOrderable) ? $isOrderable : false;
		$orderArray['isShippingRequired'] = $order->isShippingRequired();

		return $orderArray;
	}

	protected function getUpdateResponse()
	{
		$this->order->loadAll();
		ActiveRecordModel::clearArrayData();

		if ($paymentMethod = $this->session->get('OrderPaymentMethod_' . $this->order->getID()))
		{
			$this->order->setPaymentMethod($paymentMethod);
			$this->order->getTotal(true);
		}

		// @todo: sometimes the shipping address disappears (for registered users that might already have the shipping address entered before)
		if (!$this->order->shippingAddress->get() && $this->isShippingRequired($this->order) && $this->user->defaultShippingAddress->get())
		{
			$this->user->defaultShippingAddress->get()->load();
			$this->order->shippingAddress->set($this->user->defaultShippingAddress->get()->userAddress->get());
			$this->order->shippingAddress->get()->load();
		}

		$response = new CompositeJSONResponse();
		$response->addAction('overview', 'onePageCheckout', 'overview');
		$response->addAction('cart', 'onePageCheckout', 'cart');
		$response->set('order', $this->getOrderValues($this->order));

		foreach (func_get_args() as $arg)
		{
			$response->addAction($arg, 'onePageCheckout', $arg);
		}

		$this->session->unsetValue('noJS');

		return $this->postProcessResponse($response);
	}

	protected function getCheckoutSteps(CustomerOrder $order)
	{
		$steps = array(1 => 'shippingAddress');
		if ($order->isShippingRequired())
		{
			$steps[] = 'shippingMethod';
		}

		if ($this->isShippingRequired($order))
		{
			$steps[] = 'billingAddress';
		}

		$steps[] = 'payment';

		return array_flip($steps);
	}

	protected function postProcessResponse(Response $response)
	{
		if ($response instanceof ActionRedirectResponse)
		{
			$response = new ActionResponse();
		}

		$response->set('steps', $this->getCheckoutSteps($this->order));
		$response->set('editableSteps', $this->getStepStatus($this->order));
		$response->set('completedSteps', $this->getStepStatus($this->order, true));

		if ($this->anonTransactionInitiated)
		{
			ActiveRecordModel::rollback();
		}

		return $response;
	}

	protected function initAnonUser()
	{
		if ($this->user->isAnonymous())
		{
			ActiveRecordModel::beginTransaction();
			$this->anonTransactionInitiated = true;
		}
	}

	protected function saveAnonUser(User $user)
	{
		$user->setID(null);
		$this->session->set('checkoutUser', $user);
		SessionUser::setUser($user);
	}

	protected function registerAnonUser()
	{
		if ($this->user->isAnonymous())
		{
			$this->order->loadAll();

			ActiveRecord::beginTransaction();
			$this->user->setPassword($this->session->get('password'));
			$this->user->resetModifiedStatus(true);
			$this->user->defaultBillingAddress->resetModifiedStatus();
			$this->user->defaultShippingAddress->resetModifiedStatus();
			if ($this->user->getSpecification())
			{
				$this->user->setSpecification(clone $this->user->getSpecification());
			}
			$this->user->save();

			foreach (array('billingAddress' => 'defaultBillingAddress', 'shippingAddress' => 'defaultShippingAddress') as $order => $key)
			{
				$address = $this->user->$key->get();
				if ($address)
				{
					$newAddress = clone $address;
					$newAddress->userAddress->set(clone $newAddress->userAddress->get());
					$newAddress->user->set($this->user);
					$this->user->$key->set($newAddress);
					$newAddress->save();

					$this->order->$order->set($newAddress->userAddress->get());
				}
			}

			$this->order->resetArrayData();

			// shipping and billing addresses the same? save only the billing address
			if ($this->order->shippingAddress->get() && ($this->order->billingAddress->get()->toString() == $this->order->shippingAddress->get()->toString()))
			{
				$this->user->defaultShippingAddress->get()->delete();
				$this->user->defaultShippingAddress->setNull();
			}

			$this->user->save();
			$this->order->user->set($this->user);
			$this->order->user->setAsModified();

			SessionUser::setUser($this->user);
			$this->session->set('checkoutUser', null);

			ActiveRecord::commit();

			$this->getUserController()->sendWelcomeEmail($this->user);
		}
	}
}

?>
