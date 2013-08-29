<?php


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
	protected $isInitialized = false;

	const CUSTOM_FIELDS_STEP = 'SHIPPING_ADDRESS_STEP';

	public function initialize()
	{
		static $isInitialized = false;

		if ($isInitialized)
		{
			return;
		}

		$isInitialized = true;

		if ($this->config->get('CHECKOUT_METHOD') == 'CHECKOUT_MULTISTEP')
		{
			return new ActionRedirectResponse('checkout', 'index');
		}

		$this->isTosRequired = $this->config->get('REQUIRE_TOS');

		parent::initialize();
		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', self::CUSTOM_FIELDS_STEP);
		$this->config->setRuntime('ENABLE_CHECKOUTDELIVERYSTEP', true);
		$this->config->setRuntime('DISABLE_CHECKOUT_ADDRESS_STEP', false);
		$this->config->setRuntime('DISABLE_GUEST_CHECKOUT', false);
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

			$this->setAnonAddresses();

			$this->order->user->set($this->user);
			$this->order->shippingAddress->resetModifiedStatus();
			$this->order->billingAddress->resetModifiedStatus();
			$this->order->user->resetModifiedStatus();
		}
		else
		{

			$this->user->load();
			$this->user->loadAddresses();

			$address = $this->user->defaultShippingAddress;
			if (!$address)
			{
				$address = $this->user->defaultBillingAddress;
			}

			if (!$this->order->shippingAddress && $address && $this->isShippingRequired($this->order))
			{
				$userAddress = $address->userAddress;
				$this->order->shippingAddress->set($userAddress);
				$this->order->save();
			}

			$address = $this->user->defaultBillingAddress;
			if (!$this->order->billingAddress && $address)
			{
				$userAddress = $address->userAddress;
				$this->order->billingAddress->set($userAddress);
				$this->order->save();
			}
		}
	}

	public function indexAction()
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
			unset($blocks['shippingAddress']);
		}

		foreach ($blocks as $block => $key)
		{
			$response->addAction($block, 'onePageCheckout', $block);
		}

		$response->set('orderValues', $this->getOrderValues($this->order));

		return $response;
	}

	public function loginAction()
	{
		if (!$this->user->isAnonymous())
		{
			return;
		}

		$response = new ActionResponse();
		if ($this->request->gget('failedLogin'))
		{
			$response->set('failedLogin', true);
		}

		$response->set('preview_options', $this->translate('_new_customer'));

		return $response;
	}

	public function shippingAddressAction()
	{
		$sameAddress = $this->config->get('REQUIRE_SAME_ADDRESS');
		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', false);

		if ($this->user->isAnonymous())
		{
			$response = $this->getUserController()->checkout();
			$form = $response->get('form');

			if ($this->user->defaultShippingAddress)
			{
				$addressInstance = $this->user->defaultShippingAddress->userAddress;
				$shippingAddress = $addressInstance->toFlatArray();

				foreach ($shippingAddress as $key => $value)
				{
					$form->set('shipping_' . $key, $value);
				}

				if (isset($shippingAddress['State']))
				{
					$form->set('shipping_state_select', $shippingAddress['State']);
				}

				$form->set('shipping_country', $shippingAddress['countryID']);

				if ($spec = $addressInstance->getSpecification())
				{
					$form->setData($spec->getFormData('shipping_'));
				}

				$response->set('shippingStates', $this->getStateList($form->get('shipping_country')));
			}

			$form->setData($this->user->toFlatArray());
			if ($spec = $this->user->getSpecification())
			{
				$form->setData($spec->getFormData());
			}

			//$form->set('password', '');

			$this->order->getSpecification()->setFormResponse($response, $form, 'order_');
		}
		else
		{
			$this->request->set('step', 'shipping');
			$response = parent::selectAddress();

			if (!$response instanceof ActionResponse)
			{
				return null;
			}

			$response->set('step', 'shipping');
		}

		if ($this->isSameAddress())
		{
			//$form->set('sameAsBilling', true);
		}

		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', $sameAddress);

		if ($this->order->shippingAddress)
		{
			$response->set('preview_shipping', $this->order->shippingAddress->toArray());
		}

		return $this->postProcessResponse($response);
	}

	public function billingAddressAction()
	{
		$this->request->set('step', 'billing');

		$response = $this->postProcessResponse(parent::selectAddress());

		$response->get('form')->set('email', $this->user->email);

		if ($this->order->billingAddress)
		{
			$response->set('preview_billing', $this->order->billingAddress->toArray());
		}

		return $response;
	}

	public function shippingMethodsAction()
	{
		// shipping methods won't be displayed if custom fields are not filled
		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', 'SHIPPING_METHOD_STEP');

		$this->setAnonAddresses();

		/*
		$tempShipping = false;
		if (!$this->order->shippingAddress)
		{
			$this->order->shippingAddress->set($this->order->billingAddress ? $this->order->billingAddress : SessionOrder::getEstimateAddress());
			$tempShipping = true;
		}

		$tempBilling = false;
		if (!$this->order->billingAddress)
		{
			$this->order->billingAddress->set($this->order->shippingAddress);
			$tempBilling = true;
		}
		*/

		/*
		foreach ($this->order->getShipments() as $shipment)
		{
			unset($shipment->taxes);
			$shipment->getTaxes();
		}
		*/

		$response = $this->shipping();
		$this->order->serializeShipments();

		/*
		if ($tempShipping)
		{
			$this->order->shippingAddress->setNull();
		}

		if ($tempBilling)
		{
			$this->order->billingAddress->setNull();
		}
		*/

		$this->order->save();

		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', self::CUSTOM_FIELDS_STEP);

		$previewRates = array();
		foreach ($this->order->getShipments() as $shipment)
		{
			$arr = $shipment->toArray();
			if (!empty($arr['selectedRate']))
			{
				$previewRates[] = $arr['selectedRate'];
			}
		}

		if ($previewRates)
		{
			$response->set('preview_shipping_methods', $previewRates);
		}

		return $this->postProcessResponse($response);
	}

	public function cartAction()
	{
		return $this->postProcessResponse($this->getOrderController()->index());
	}

	public function overviewAction()
	{
		if (!$this->order->isShippingRequired())
		{
			$this->order->shippingAddress->setNull();
		}

		$this->order->resetArrayData();
		$array = $this->order->toArray();

		if ($this->config->get('REQUIRE_SAME_ADDRESS') && isset($array['ShippingAddress']))
		{
			$array['BillingAddress'] = $array['ShippingAddress'];
		}

		return $this->postProcessResponse(new ActionResponse('order', $array));
	}

	public function paymentAction()
	{
		if ($this->config->get('REQUIRE_SAME_ADDRESS'))
		{
			$this->order->shippingAddress->set($this->order->billingAddress);
			$this->order->shippingAddress->resetModifiedStatus();
		}

		$this->ignoreValidation = true;
		$response = $this->postProcessResponse($this->pay());
		$this->ignoreValidation = false;

		$paymentMethodForm = new Form($this->getPaymentMethodValidator());
		if ($this->isTosRequired)
		{
			$paymentMethodForm->set('tos', $this->session->get('tos'));
		}

		$paymentMethodForm->set('payMethod', $this->session->get('paymentMethod'));

		$response->set('form', $paymentMethodForm);
		$response->set('selectedMethod', $this->session->get('paymentMethod'));
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

	public function doProceedRegistrationAction()
	{
		$this->setSessionData('isProceeded', true);

		return $this->getUpdateResponse();
	}

	public function doLoginAction()
	{
		$this->setSessionData('isProceeded', false);
		$res = $this->getUserController()->doLogin();
		$params = array();
		if ($res->getActionName() == 'login')
		{
			$params = array('query' => 'failedLogin=true');
		}

		return new ActionRedirectResponse('onePageCheckout', 'index', $params);
	}

	public function doSelectShippingMethodAction()
	{
		parent::doSelectShippingMethod();

		return new ActionRedirectResponse('onePageCheckout', 'update');
	}

	public function updateAction()
	{
		return $this->getUpdateResponse('shippingMethods', 'billingAddress', 'shippingAddress');
	}

	public function doSelectShippingAddressAction()
	{
		$sameAddress = $this->config->get('REQUIRE_SAME_ADDRESS');
		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', false);

		$this->order->loadAll();

		$this->request->set('step', 'shipping');

		$this->initAnonUser();

		if ($this->user->isAnonymous())
		{
			try
			{
				$billing = $this->user->defaultBillingAddress;
				if ($err = $this->setAddress('shipping'))
				{
					return $err;
				}
			}

			// billing address ID is not in DB
			catch (Exception $e)
			{
				$this->user->defaultBillingAddress->set($billing);
				$this->user->defaultShippingAddress->set($this->lastAddress);

				if ($this->user->defaultShippingAddress)
				{
					$this->order->shippingAddress->set($this->user->defaultShippingAddress->userAddress);
				}

				if ($this->order->shippingAddress && ($this->order->shippingAddress->getID() == $this->order->billingAddress->getID()))
				{
					$shipping = clone $this->user->defaultBillingAddress->userAddress;
					if (!$this->user->defaultShippingAddress)
					{
						$this->user->defaultShippingAddress->set(ShippingAddress::getNewInstance($this->user, $shipping));
					}

					$this->user->defaultShippingAddress->userAddress->set($shipping);
				}

				$this->saveAnonUser($this->user);
				$this->order->shippingAddress->resetModifiedStatus();
				SessionOrder::save($this->order);
			}
		}
		else
		{
			if ($err = $this->setAddress('shipping'))
			{
				return $err;
			}

			// UserAddress::toString() uses old data otherwise
			if ($this->order->shippingAddress)
			{
				$this->order->shippingAddress->resetArrayData();

				if ($sameAddress)
				{
					$this->order->billingAddress->set($this->order->shippingAddress);
					$this->order->save();
				}
				else if ($this->request->gget('sameAsShipping') &&
						((!$this->order->billingAddress && $this->order->shippingAddress && $this->order->isShippingRequired()) ||
						($this->order->billingAddress && $this->order->shippingAddress && ($this->order->billingAddress->toString() != $this->order->shippingAddress->toString()))))
				{
					$this->order->billingAddress->set(clone $this->order->shippingAddress);
					$this->order->billingAddress->save();
					$this->order->save();
				}
			}
		}

		// attempt to pre-select a shipping method
		ActiveRecord::clearPool();
		$this->config->resetRuntime('DELIVERY_TAX_CLASS');
		$this->order = CustomerOrder::getInstanceById($this->order->getID(), true);

		if (isset($anonOrder))
		{
			$this->order->shippingAddress->set($anonOrder->shippingAddress);
			$this->order->billingAddress->set($anonOrder->billingAddress);
		}

		// @todo: needs to be called twice for the auto-selection to get saved
		$this->init();
		$this->shippingMethods();

		$this->config->setRuntime('REQUIRE_SAME_ADDRESS', $sameAddress);

		return new ActionRedirectResponse('onePageCheckout', 'update');

		//return $this->getUpdateResponse('shippingMethods', 'shippingAddress', 'billingAddress');
	}

	public function doSelectBillingAddressAction()
	{
		$this->order->loadAll();
		$this->request->set('step', 'billing');
		$this->initAnonUser();

		$shipments = $this->order->getShipments();

		if ($this->user->isAnonymous())
		{
			$shipping = $this->user->defaultShippingAddress;

			$this->session->set('newsletter', $this->request->gget('newsletter'));
			$this->request->set('sameAsBilling', true);

			$controller = $this->getUserController();
			$response = $controller->processCheckoutRegistration();
			$user = $controller->getUser();

			$this->order->shippingAddress->setNull();
			$user->defaultShippingAddress->setNull();

			if ($shipping)
			{
				$this->order->shippingAddress->set($shipping->userAddress);
				$this->order->shippingAddress->resetModifiedStatus();
				$user->defaultShippingAddress->set($shipping);
			}

			ActiveRecordModel::rollback();

			$this->order->getSpecification()->loadRequestData($this->request, 'order_');
			$this->order->getSpecification()->save();

			if ($this->order->getSpecification()->hasValues())
			{
				if (!$this->order->eavObject->getID())
				{
					$this->order->eavObject->save();
				}

				$this->order->eavObject->setAsModified(true);
			}

			$this->order->save();

			ActiveRecordModel::commit();
			$this->anonTransactionInitiated = false;

			if ($response->getActionName() == 'checkout')
			{
				$errorResponse = $controller->checkout();
				$errors = $errorResponse->get('form')->getValidator()->getErrorList();
				return new JSONResponse(array('errorList' => $errors));
			}

			$this->saveAnonUser($user);
		}
		else
		{
			if ($res = $this->setAddress('shipping'))
			{
				return $res;
			}
		}

		return new ActionRedirectResponse('onePageCheckout', 'update');
		//return $this->getUpdateResponse('shippingAddress', 'billingAddress');
	}

	protected function setAddress($step)
	{
		$res = parent::doSelectAddress();

		if ($res instanceof ActionRedirectResponse && ($res->getActionName() == 'selectAddress'))
		{
			$params = $res->getParamList();
			if (empty($params['step']) || ($step != $params['step']))
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

	public function updateCartAction()
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

	public function fallbackAction()
	{
		$this->session->set('noJS', true);
		return new ActionRedirectResponse('checkout', 'index');
	}

	public function setPaymentMethodAction()
	{
		$this->order->loadAll();

		// @todo: in case only one shipping rate is available, it is unselected when setting payment method unless the shipping() method is called
		$this->shipping();

		$method = $this->request->gget('payMethod');
		if ('cc' == $method)
		{
			$method = $this->config->get('CC_HANDLER');
		}

		//$this->session->set('OrderPaymentMethod_' . $this->order->getID(), $method);
		$this->order->setPaymentMethod($method);

		$this->order->getTotal(true);

		return $this->getUpdateResponse();
	}

	public function payCreditCardAction()
	{
		$this->session->set('paymentMethod', 'cc');
		$this->beforePayment();
		return parent::payCreditCard();
	}

	public function redirectAction()
	{
		$this->session->set('paymentMethod', $this->request->gget('id'));
		$this->beforePayment();
		return parent::redirect();
	}

	public function payOfflineAction()
	{
		$this->session->set('paymentMethod', $this->request->gget('id'));
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

		// reload order data
		$this->order->save();

		ActiveRecord::clearPool();

		$this->order = CustomerOrder::getInstanceById($this->order->getID(), true);
		$this->order->loadAll();
		$this->order->getTotal(true);
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
		if (!$order->shippingAddress || $order->billingAddress)
		{
			$this->setAnonAddresses();
		}

		$isStepEditable = $isCompleted;

		// validation will return false for all steps if custom fields are not filled
		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', 'SHIPPING_METHOD_STEP');

		$res = array('login' => false,
					 'billingAddress' => !$this->validateOrder($order, self::STEP_ADDRESS + $isStepEditable),
					 'shippingAddress' => !$this->validateOrder($order, self::STEP_ADDRESS + $isStepEditable),
					 'shippingMethod' => !$this->validateOrder($order, self::STEP_SHIPPING + $isStepEditable),
					 'payment' => !$this->validateOrder($order, self::STEP_PAYMENT));

		$this->config->setRuntime('CHECKOUT_CUSTOM_FIELDS', self::CUSTOM_FIELDS_STEP);

		if (!$order->shippingAddress && !$this->user->defaultShippingAddress && $this->order->isShippingRequired())
		{
			if ($isCompleted)
			{
				$res['shippingAddress'] = false;
			}
		}

		if (!$order->billingAddress && !$this->user->defaultBillingAddress && !($this->config->get('REQUIRE_SAME_ADDRESS') && $order->shippingAddress))
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

		if ($this->user->getID() || $this->getSessionData('isProceeded'))
		{
			$res['login'] = true;
		}

		if (($this->user->getID() || $this->getSessionData('isProceeded')) && !$isStepEditable)
		{
			$res['billingAddress'] = true;
		}

		if ($order->billingAddress)
		{
			$res['billingAddress'] = true;
		}

		if ($order->billingAddress && $isStepEditable)
		{
			//$res['shippingAddress'] = true;
		}

		if ($isStepEditable && !$order->shippingAddress)
		{
			//$res['shippingAddress'] = false;
		}

		return $res;
	}

	protected function checkAccess()
	{
		return true;
	}

	protected function validateOrder(CustomerOrder $order, $step = 0)
	{
		if ($this->config->get('REQUIRE_SAME_ADDRESS') && !$order->billingAddress)
		{
			$order->billingAddress->set($order->shippingAddress);
			$tempBilling = true;
		}

		$res = !$this->ignoreValidation ? parent::validateOrder($order, $step) : false;

		if (!empty($tempBilling))
		{
			$order->billingAddress->setNull();
		}

		return $res;
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
		if ($this->user->isAnonymous())
		{
			$anonOrder = $this->order;
		}

		/////// @todo - should be a better way for recalculating taxes...
		ActiveRecord::clearPool();
		$this->config->resetRuntime('DELIVERY_TAX_CLASS');
		$this->order = CustomerOrder::getInstanceById($this->order->getID(), true);
		///////

		if (isset($anonOrder))
		{
			$this->order->shippingAddress->set($anonOrder->shippingAddress);
			$this->order->billingAddress->set($anonOrder->billingAddress);
		}

		$this->order->loadAll();
		$this->restoreShippingMethodSelection();
		ActiveRecordModel::clearArrayData();

		if ($paymentMethod = $this->session->get('OrderPaymentMethod_' . $this->order->getID()))
		{
			$this->order->setPaymentMethod($paymentMethod);
			$this->order->getTotal(true);
		}

		$this->setAnonAddresses();

		// @todo: sometimes the shipping address disappears (for registered users that might already have the shipping address entered before)
		if (!$this->order->shippingAddress && $this->isShippingRequired($this->order) && $this->user->defaultShippingAddress)
		{
			$this->user->defaultShippingAddress->load();
			$this->order->shippingAddress->set($this->user->defaultShippingAddress->userAddress);

			if ($this->order->shippingAddress)
			{
				$this->order->shippingAddress->load();
			}
		}

		$response = new CompositeJSONResponse();
		$response->addAction('overview', 'onePageCheckout', 'overview');
		$response->addAction('cart', 'onePageCheckout', 'cart');

		if ($this->request->getActionName() != 'setPaymentMethod')
		{
			$response->addAction('payment', 'onePageCheckout', 'payment');
		}

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
		$steps = array(1 + ($this->user->isAnonymous()) => 'billingAddress');
		if ($order->isShippingRequired())
		{
			$steps[] = 'shippingAddress';
			$steps[] = 'shippingMethod';
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
			$this->anonTransactionInitiated = false;
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
		$this->session->set('checkoutUser', serialize($user));
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
				$address = $this->user->$key;
				if ($address)
				{
					$newAddress = clone $address;
					$newAddress->userAddress->set(clone $newAddress->userAddress);
					$newAddress->user->set($this->user);
					$this->user->$key->set($newAddress);
					$newAddress->save();

					$this->order->$order->set($newAddress->userAddress);
				}
			}

			$this->order->resetArrayData();

			// shipping and billing addresses the same? save only the billing address
			if ($this->isSameAddress())
			{
				$this->user->defaultShippingAddress->delete();
				$this->user->defaultShippingAddress->setNull();
			}

			$this->user->save();
			$this->order->user->set($this->user);
			$this->order->user->setAsModified();

			SessionUser::setUser($this->user);
			$this->session->set('checkoutUser', null);

			if ($this->session->get('newsletter'))
			{
				$sub = $this->user->getRelatedRecordSet('NewsletterSubscriber')->shift();
				if ($sub)
				{
					$sub->isEnabled->set(true);
					$sub->save();
				}
			}

			ActiveRecord::commit();
			$this->anonTransactionInitiated = false;

			$this->getUserController()->sendWelcomeEmail($this->user);
		}
	}

	private function isSameAddress()
	{
		return $this->order->shippingAddress && ($this->order->billingAddress->toString() == $this->order->shippingAddress->toString());
	}

	private function setAnonAddresses()
	{
		if ($this->user->isAnonymous())
		{
			if ($this->user->defaultBillingAddress)
			{
				$billingAddress = $this->user->defaultBillingAddress->userAddress;
			}

			if ($this->user->defaultBillingAddress && !$this->order->billingAddress)
			{
				if ($billingAddress)
				{
					$this->order->billingAddress->set($billingAddress);
				}
			}

			if ($this->isShippingRequired($this->order) && !$this->order->shippingAddress)
			{
				if ($this->config->get('REQUIRE_SAME_ADDRESS'))
				{
					if (!empty($billingAddress))
					{
						$this->order->shippingAddress->set($billingAddress);
					}
				}
				else if ($this->user->defaultShippingAddress)
				{
					// same shipping address?
					if (!$this->user->defaultShippingAddress)
					{
						$this->order->shippingAddress->set($billingAddress);
						$this->user->defaultShippingAddress->userAddress->set(clone $billingAddress);
					}
					else
					{
						$address = $this->user->defaultShippingAddress->userAddress;
						$this->order->shippingAddress->set($address);
					}
				}
			}

			$this->order->shippingAddress->resetModifiedStatus();
			$this->order->billingAddress->resetModifiedStatus();
		}
	}
}

?>
