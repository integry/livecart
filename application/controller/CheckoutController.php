<?php


/**
 *  Handles order checkout process
 *
 *  The order checkout consists of the following steps:
 *
 *  1. Determine user status
 *
 *	  If the user is logged in, this step is skipped
 *	  If the user is not logged in there are 2 or 3 choices depending on configuration:
 *		  a) log in
 *		  b) create a new user account
 *		  c) continue checkout without registration (anonymous checkout).
 *			 In this case the user account will be created automatically
 *
 *  2. Process login
 *
 *	  If the user is already logged in or is checking out anonymously this step is skipped.
 *
 *  3. Select or enter billing and shipping addresses
 *
 *	  If the user has just been registered, this step is skipped, as these addresses have already been provided
 *	  If the user was logged in, the billing and shipping addresses have to be selected (or new addresses entered/edited)
 *
 *  4. Select shipping method and calculate tax
 *
 *	  Based on the shipping addresses, determine the available shipping methods and costs.
 *	  Based on the shipping or billing address (depending on config), calculate taxes if any.
 *
 *  5. Confirm order totals and select payment method
 *
 *  6. Enter payment details
 *
 *	  Redirected to external site if it's a 3rd party payment processor (like Paypal)
 *	  This step is skipped if a non-online payment method is selected (check, wire transfer, phone, etc.)
 *
 *  7. Process payment and reserve products
 *
 *	  This step is skipped also if the payment wasn't made
 *	  If the payment was attempted, but unsuccessful, return to payment form (6)
 *
 *  8. Process order and send invoice (optional)
 *
 *	  Whether the order is processed, depends on the configuration (auto vs manual processing)
 *
 *  9. Show the order confirmation page
 *
 * @author Integry Systems
 * @package application/controller
 */
class CheckoutController extends FrontendController
{
	const STEP_ADDRESS = 3;
	const STEP_SHIPPING = 4;
	const STEP_PAYMENT = 5;

	public function initialize()
	{
		if ('CheckoutController' == get_class($this) && ($this->config->get('CHECKOUT_METHOD') == 'CHECKOUT_ONEPAGE'))
		{
			if (in_array($this->router->getActionName(), array('index', 'selectAddress', 'shipping', 'pay')))
			{
				if (!$this->order->isMultiAddress && !$this->session->get('noJS'))
				{
					return new ActionRedirectResponse('onePageCheckout', 'index');
				}
			}
		}

		$this->loadLanguageFile('User');

		parent::initialize();
		$this->addBreadCrumb($this->translate('_checkout'), $this->url->get('order/index'), true));

		$action = $this->router->getActionName();

		if ('index' == $action)
		{
			return false;
		}

		$this->addBreadCrumb($this->translate('_select_addresses'), $this->url->get('checkout/selectAddress'), true));

		if ('selectAddress' == $action)
		{
			return false;
		}

		$this->addBreadCrumb($this->translate('_shipping'), $this->url->get('checkout/shipping'), true));

		if ('shipping' == $action)
		{
			return false;
		}

		$this->addBreadCrumb($this->translate('_pay'), $this->url->get('checkout/pay'), true));
	}

	/**
	 *  1. Determine user status
	 */
	public function indexAction()
	{
		if ($this->user->isLoggedIn())
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
	 *  Redirect to an external site to acquire customer information and initiate payment (express checkout)
	 */
	public function expressAction()
	{
		// redirect to external site
		$class = $this->request->get('id');
		$this->order->setPaymentMethod($class);
		$handler = $this->application->getExpressPaymentHandler($class, $this->getTransaction());
		$handler->setOrder($this->order);

		$returnUrl = $this->router->createFullUrl($this->url->get('checkout/expressReturn', 'id' => $class), true));
		$cancelUrl = $this->router->createFullUrl($this->url->get('order'), true));
		$url = $handler->getInitUrl($returnUrl, $cancelUrl, !$handler->getConfigValue('AUTHONLY'));
		$this->order->setCheckoutStep(CustomerOrder::CHECKOUT_PAY);

		return new RedirectResponse($url);
	}

	public function expressReturnAction()
	{
		$class = $this->request->get('id');
		$this->order->setPaymentMethod($class);

		$handler = $this->application->getExpressPaymentHandler($class, $this->getTransaction());
		$handler->setOrder($this->order);

		$details = $handler->getTransactionDetails($this->request->toArray());

		$address = UserAddress::getNewInstanceByTransaction($details);
		$address->save();

		$paymentData = array_diff_key($this->request->toArray(), array_flip(array('controller', 'action', 'id', 'route', 'PHPSESSID')));

		// @todo - determine if the order is new or completed earlier, but unpaid
		// for now only new orders can be paid with express checkout methods
		$order = $this->getPaymentOrder();
		$express = ExpressCheckout::getNewInstance($order, $handler);
		$express->address->set($address);
		$express->paymentData->set(serialize($paymentData));
		$express->save();

		// auto-login user if anonymous
		if ($this->user->isAnonymous())
		{
			// create new user account if it doesn't exist
			if (!($user = User::getInstanceByEmail($details->email)))
			{
				$user = User::getNewInstance($details->email);
				$user->firstName->set($details->firstName);
				$user->lastName->set($details->lastName);
				$user->companyName->set($details->companyName);
				$user->isEnabled->set(true);
				$user->save();
			}

			$this->sessionUser->setUser($user);
			$order->setUser($user);
		}

		$order->billingAddress->set($address);

		if ($order->isShippingRequired())
		{
			$order->shippingAddress->set($address);
		}

		$order->save();

		return new ActionRedirectResponse('checkout', 'shipping');
	}

	/**
	 *  3. Select or enter billing and shipping addresses
	 *	@role login
	 */
	public function selectAddressAction()
	{
		$this->user->loadAddresses();

		$step = $this->config->get('ENABLE_CHECKOUTDELIVERYSTEP') ? $this->request->get('step', 'billing') : null;

		if ($this->config->get('REQUIRE_SAME_ADDRESS') && ('shipping' == $step))
		{
			return new ActionRedirectResponse('checkout', 'shipping');
		}

		// address step disabled?
		if ($this->config->get('DISABLE_CHECKOUT_ADDRESS_STEP'))
		{
			if ($this->user->defaultBillingAddress)
			{
				$this->order->billingAddress->set($this->user->defaultBillingAddress->userAddress);
			}

			if ($this->user->defaultShippingAddress && $this->order->isShippingRequired())
			{
				$this->order->shippingAddress->set($this->user->defaultShippingAddress->userAddress);
			}

			$this->order->save();

			if (!$this->order->shippingAddress && $this->isShippingRequired($this->order))
			{
				$step = 'shipping';
			}
			else
			{
				return new ActionRedirectResponse('checkout', 'pay');
			}
		}

		if ($redirect = $this->validateOrder($this->order))
		{
			return $redirect;
		}

		$form = $this->buildAddressSelectorForm($this->order, $step);

		if ($this->order->billingAddress)
		{
			$form->set('billingAddress', $this->order->billingAddress->getID());
		}
		else
		{
			if ($this->user->defaultBillingAddress)
			{
				$form->set('billingAddress', $this->user->defaultBillingAddress->userAddress->getID());
			}
		}

		if ($this->order->shippingAddress)
		{
			$form->set('shippingAddress', $this->order->shippingAddress->getID());
		}
		else
		{
			if ($this->user->defaultShippingAddress)
			{
				$form->set('shippingAddress', $this->user->defaultShippingAddress->userAddress->getID());
			}
		}

		if (!$form->get('checkbox_sameAsBilling'))
		{
			$form->set('sameAsBilling', (int)($form->get('billingAddress') == $form->get('shippingAddress') || !$this->user->defaultShippingAddress));
		}

		foreach (array('firstName', 'lastName') as $name)
		{
			$var = 'billing_' . $name;
			if (!$form->get($var))
			{
				$form->set($var, $this->user->$name);
			}
		}



		foreach (array('billing' => $this->user->getBillingAddressArray(),
						'shipping' => $this->user->getShippingAddressArray()) as $type => $addresses)
		{
			if (count($addresses) > 1)
			{
				$this->set($type . 'Addresses', $addresses);
			}
			else if (count($addresses) == 1)
			{
				$address = $addresses[0]['UserAddress'];
				$address['country'] = $address['countryID'];

				if (isset($address['stateID']))
				{
					$address['state_select'] = $address['stateID'];
				}

				if (!empty($address['State']['name']))
				{
					$address['stateName'] = $address['State']['name'];
				}

				if (isset($address['stateName']))
				{
					$address['state_text'] = $address['stateName'];
				}

				foreach ($address as $key => $value)
				{
					$form->set($type . '_' . $key, $value);
				}
			}
		}

		$this->set('form', $form);
		$this->set('order', $this->order->toArray());
		$this->set('countries', $this->getCountryList($form));
		$this->set('billing_states', $this->getStateList($form->get('billing_country')));
		$this->set('shipping_states', $this->getStateList($form->get('shipping_country')));
		$this->set('step', $step);

		$this->order->getSpecification()->setFormResponse($response, $form);

		foreach (array('billing', 'shipping') as $type)
		{
			if ($id = $form->get($type . 'Address'))
			{
				try
				{
					$address = ActiveRecordModel::getInstanceByID('UserAddress', $id, true);
				}
				catch (ARNotFoundException $e)
				{
					$address = UserAddress::getNewInstance();
				}
			}
			else
			{
				$address = UserAddress::getNewInstance();
			}

			$address->getSpecification()->setFormResponse($response, $form, $type . '_');
		}

	}

	/**
	 *	@role login
	 */
	public function doSelectAddressAction()
	{
		$this->user->loadAddresses();

		$step = $this->request->get('step');

		$validator = $this->buildAddressSelectorValidator($this->order, $step);
		if (!$validator->isValid())
		{
			return new ActionRedirectResponse('checkout', 'selectAddress', array('query' => array('step' => $step)));
		}

		// create or edit addresses
		$types = array('billing', 'shipping');
		if ($this->request->get('sameAsBilling') || $this->order->isMultiAddress)
		{
			unset($types[1]);
		}

		foreach ($types as $type)
		{
			if ((!$step || ($type == $step)) && !$this->request->get($type . 'Address'))
			{
				$addressField = 'default' . ucfirst($type). 'Address';
				if ($address = $this->user->$addressField)
				{
					$address->load();
					$this->saveAddress($address->userAddress, $type . '_');
				}
				else
				{
					$address = $this->createAddress(ucfirst($type) . 'Address', $type . '_');
				}

				$this->lastAddress = $address;

				$this->request->set($type . 'Address', $address->userAddress->getID());
			}
		}

		try
		{
			if (!$step || ('billing' == $step))
			{
				if (!$this->user->isAnonymous())
				{
					$f = new ARSelectFilter();
					$f->setCondition(new EqualsCond(new ARFieldHandle('BillingAddress', 'userID'), $this->user->getID()));
					$f->mergeCondition(new EqualsCond(new ARFieldHandle('BillingAddress', 'userAddressID'), $this->request->get('billingAddress')));
					$r = ActiveRecordModel::getRecordSet('BillingAddress', $f, array('UserAddress'));

					if ($r->size())
					{
						$billing = $r->get(0);
					}
					else if (!($billing = $this->user->defaultBillingAddress))
					{
						throw new ApplicationException('Invalid billing address');
					}

					$address = $billing->userAddress;
				}
				else
				{
					$address = $address->userAddress;
				}

				$this->order->billingAddress->set($address);
			}

			// shipping address
			if ($this->isShippingRequired($this->order) && !$this->order->isMultiAddress && (!$step || ('shipping' == $step)))
			{
				if ($this->request->get('sameAsBilling'))
				{
					if (isset($billing))
					{
						$shipping = $billing->userAddress;
					}
					else
					{
						$shipping = $this->order->billingAddress;
					}
				}
				else
				{
					$f = new ARSelectFilter();
					$f->setCondition(new EqualsCond(new ARFieldHandle('ShippingAddress', 'userID'), $this->user->getID()));
					$f->mergeCondition(new EqualsCond(new ARFieldHandle('ShippingAddress', 'userAddressID'), $this->request->get('shippingAddress')));
					$r = ActiveRecordModel::getRecordSet('ShippingAddress', $f, array('UserAddress'));

					if (!$r->size())
					{
						throw new ApplicationException('Invalid shipping address');
					}

					$shipping = $r->get(0)->userAddress;
				}

				$this->order->shippingAddress->set($shipping);
				SessionOrder::setEstimateAddress($shipping);
			}

			if ($this->config->get('REQUIRE_SAME_ADDRESS') && $this->order->isShippingRequired())
			{
				$this->order->shippingAddress->set($this->order->billingAddress);
				$step = 'shipping';
			}

			if ('billing' != $step)
			{
				$this->order->resetShipments();
			}
		}
		catch (Exception $e)
		{
			throw $e;
			return new ActionRedirectResponse('checkout', 'selectAddress', array('query' => array('step' => $step)));
		}

		$this->order->loadRequestData($this->request);

		SessionOrder::save($this->order);

		if (('billing' == $step) && ($this->isShippingRequired($this->order) && !$this->order->isMultiAddress))
		{
			return new ActionRedirectResponse('checkout', 'selectAddress', array('query' => array('step' => 'shipping')));
		}
		else
		{
			return new ActionRedirectResponse('checkout', 'shipping');
		}
	}

	protected function restoreShippingMethodSelection()
	{
		$shipments = $this->order->getShipments();

		// get previously selected shipping methods
		$rateCache = (array)$this->session->get('SelectedShippingRates_' . $this->order->getID());

		if ($rateCache)
		{
			foreach ($shipments as $key => $shipment)
			{
				if (!empty($rateCache[$key]))
				{
					$shipment->setRateId($rateCache[$key]);
				}
			}
		}
	}

	/**
	 *  4. Select shipping methods
	 *	@role login
	 */
	public function shippingAction()
	{
		$shipments = $this->order->getShipments();

		$this->restoreShippingMethodSelection();

		if ($redirect = $this->validateOrder($this->order, self::STEP_SHIPPING))
		{
			return $redirect;
		}

		if (!$this->order->isShippingRequired())
		{
			return new ActionRedirectResponse('checkout', 'pay');
		}

		foreach($shipments as $shipment)
		{
			if(count($shipment->getItems()) == 0)
			{
				$shipment->delete();
				$shipments->removeRecord($shipment);
			}
		}

		$form = $this->buildShippingForm($shipments);

		$needSelecting = null;

		foreach ($shipments as $key => $shipment)
		{
			if (!$shipment->isShippable())
			{
				$download = $shipment;
				$downloadIndex = $key;
				$needSelecting = false;
				continue;
			}

			$shipmentRates = $shipment->getAvailableRates();

			if ($shipmentRates->size() > 1)
			{
				$needSelecting = true;
			}
			else if (!$shipmentRates->size())
			{
				$validator = $this->buildAddressSelectorValidator($this->order, 'shipping');
				$validator->triggerError('selectedAddress', $this->translate('_err_no_rates_for_address'));
				$validator->saveState();

			 	return new ActionRedirectResponse('checkout', 'selectAddress');
			}
			else
			{
				$shipment->setRateId($shipmentRates->get(0)->getServiceId());
				if ($this->order->isMultiAddress)
				{
					$shipment->save();
				}
			}

			$rates[$key] = $shipmentRates;
			if ($shipment->getSelectedRate())
			{
				$form->set('shipping_' . $key, $shipment->getSelectedRate()->getServiceID());
			}
		}

		SessionOrder::save($this->order);

		// only one shipping method for each shipment, so we pre-select it automatically
		if (is_null($needSelecting) && $this->config->get('SKIP_SHIPPING') && ($this->config->get('CHECKOUT_CUSTOM_FIELDS') != 'SHIPPING_METHOD_STEP'))
		{
			$this->order->serializeShipments();
			SessionOrder::save($this->order);
			return new ActionRedirectResponse('checkout', 'pay');
		}

		$rateArray = array();
		foreach ((array)$rates as $key => $rate)
		{
			$rateArray[$key] = $rate->toArray();
		}


		$shipmentArray = $shipments->toArray();

		if (isset($download))
		{
			$this->set('download', $download->toArray());
			unset($shipmentArray[$downloadIndex]);
		}

		$locale = self::getApplication()->getLocale();
		foreach($rateArray as &$rates)
		{
			foreach($rates as $k => &$item)
			{
				if(!empty($item['ShippingService']['deliveryTimeMinDays']))
				{
					$item['ShippingService']['formatted_deliveryTimeMinDays'] = $locale->getFormattedTime(strtotime('+'.$item['ShippingService']['deliveryTimeMinDays']. ' days'));
				}
				if(!empty($item['ShippingService']['deliveryTimeMaxDays']))
				{
					$item['ShippingService']['formatted_deliveryTimeMaxDays'] = $locale->getFormattedTime(strtotime('+'.$item['ShippingService']['deliveryTimeMaxDays']. ' days'));
				}
			}
		}
		unset($item);

		$recurringIDs = array();
		$recurringPlans = array();
		foreach ($shipmentArray as $item)
		{
			foreach($item['Order']['cartItems'] as $orderedItem)
			{
				if (isset($orderedItem['recurringID']))
				{
					$recurringIDs[] = $orderedItem['recurringID'];
				}
			}
		}
		if (count($recurringIDs))
		{
			$this->loadLanguageFile('Product'); // contains translations for recurring product pricing.
						$recurringPlans = RecurringProductPeriod::getRecordSetArrayByIDs($recurringIDs);
			$this->set('periodTypesPlural', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL));
			$this->set('periodTypesSingle', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_SINGLE));
		}

		$this->set('shipments', $shipmentArray);
		$this->set('rates', $rateArray);
		$this->set('recurringPlans', $recurringPlans);
		$this->set('currency', $this->getRequestCurrency());
		$this->set('form', $form);
		$this->set('order', $this->order->toArray());
		$this->order->getSpecification()->setFormResponse($response, $form);

		$this->order->setCheckoutStep(CustomerOrder::CHECKOUT_ADDRESS);

	}

	/*
	private function deliveryTime()
	{



		{
			if (isset($array[$name]))
			{
				$time = strtotime($array[$name]);

				if (!$time)
				{
					continue;
				}

				if (!isset($locale))
				{
					$locale = self::getApplication()->getLocale();
				}

				$array['formatted_' . $name] = $locale->getFormattedTime($time);
			}
		}

	}
*/
	/**
	 *	@role login
	 */
	public function doSelectShippingMethodAction()
	{
		$shipments = $this->order->getShipments();

		if (!$this->buildShippingValidator($shipments)->isValid())
		{
			return new ActionRedirectResponse('checkout', 'shipping');
		}

		$selectedRateCache = array();
		foreach ($shipments as $key => $shipment)
		{
			if ($shipment->isShippable())
			{
				$rates = $shipment->getAvailableRates();

				$selectedRateId = $this->request->get('shipping_' . $key);

				if (!$rates->getByServiceId($selectedRateId))
				{
					throw new ApplicationException('No rate found: ' . $key .' (' . $selectedRateId . ')');
					return new ActionRedirectResponse('checkout', 'shipping');
				}

				$shipment->setRateId($selectedRateId);

				if ($this->order->isMultiAddress)
				{
					$shipment->save();
				}

				$selectedRateCache[$key] = $selectedRateId;
			}
		}

		if (!$this->order->isMultiAddress)
		{
			$this->order->serializeShipments();
		}

		$this->order->loadRequestData($this->request);

		$this->session->set('SelectedShippingRates_' . $this->order->getID(), $selectedRateCache);

		SessionOrder::save($this->order);

		return new ActionRedirectResponse('checkout', 'pay');
	}

	/**
	 *  5. Make payment
	 *	@role login
	 */
	public function payAction()
	{
		$this->order->loadAll();
		$this->order->getSpecification();
		$this->order->getTotal(true);

		if ($this->config->get('REQUIRE_SAME_ADDRESS'))
		{
			$this->order->shippingAddress->set($this->order->billingAddress);

			if (!$this->user->isAnonymous())
			{
				$this->order->save();
			}
			else
			{
				$this->order->shippingAddress->resetModifiedStatus();
			}
		}

		// @todo: variation prices appear as 0.00 without the extra toArray() call :/
		$this->order->toArray();

		if ($redirect = $this->validateOrder($this->order, self::STEP_PAYMENT))
		{
			return $redirect;
		}

		// @todo: the addresses should be loaded automatically...
		foreach ($this->order->getShipments() as $shipment)
		{
			if ($shipment->shippingAddress)
			{
				$shipment->shippingAddress->load();
			}
		}

		// check for express checkout data for this order
		if (ExpressCheckout::getInstanceByOrder($this->order))
		{
			return new ActionRedirectResponse('checkout', 'payExpress');
		}

		$currency = $this->request->get('currency', $this->application->getDefaultCurrencyCode());


		$this->set('order', $this->order->toArray());
		$this->set('currency', $this->getRequestCurrency());
		$this->set('error', strip_tags($this->request->get('error')));

		// offline payment methods
		$offlineMethods = OfflineTransactionHandler::getEnabledMethods();
		$this->set('offlineMethods', $offlineMethods);
		$this->set('offlineForms', $this->getOfflinePaymentForms($response));

		$this->setPaymentMethodResponse($response, $this->order);
		$this->order->getSpecification()->setFormResponse($response, $response->get('ccForm'));

		$external = $this->application->getPaymentHandlerList(true);

		// auto redirect to external payment page if only one handler is enabled
		if ($this->config->get('SKIP_PAYMENT'))
		{
			if (1 == count($external) && !$offlineMethods && !$this->config->get('CC_ENABLE') && !$response->get('ccForm')->getValidator()->getErrorList())
			{
				$this->request->set('id', $external[0]);
				$redirect = $this->redirect();

				if ($redirect instanceof ActionResponse)
				{
					$redirect = new ActionRedirectResponse('checkout', 'redirect', array('id' => $external[0]));
				}

				return $redirect;
			}
		}

		$this->order->setCheckoutStep(CustomerOrder::CHECKOUT_SHIPPING);

	}

	public function setPaymentMethodResponseAction(ActionResponse $response, CustomerOrder $order)
	{
		$ccHandler = $this->application->getCreditCardHandler();
		$ccForm = $this->buildCreditCardForm($ccHandler);

		if ($order->billingAddress)
		{
			$ccForm->set('ccName', $order->billingAddress->getFullName());
		}

		$this->set('ccForm', $ccForm);
		if ($ccHandler)
		{
			$this->set('ccHandler', $ccHandler->toArray());

			$months = range(1, 12);
			$months = array_combine($months, $months);

			$years = range(date('Y'), date('Y') + 20);
			$years = array_combine($years, $years);

			$this->set('months', $months);
			$this->set('years', $years);
			$this->set('ccTypes', $this->application->getCardTypes($ccHandler));

			$eavManager = new EavSpecificationManager(EavObject::getInstanceByIdentifier('creditcard'));
			$eavManager->setFormResponse($response, $ccForm);
			foreach (array('groupClass', 'specFieldList') as $vars)
			{
				$ccVars[$vars] = $response->get($vars);
			}

			$this->set('ccVars', $ccVars);
		}

		// other payment methods
		$external = $this->application->getPaymentHandlerList(true);
		$this->set('otherMethods', $external);
	}

	private function getOfflinePaymentForms(ActionResponse $response)
	{
		$forms = array();
		$offlineVars = array();
		foreach (OfflineTransactionHandler::getEnabledMethods() as $method)
		{
			$forms[$method] = new Form($this->getOfflinePaymentValidator($method));
			$eavManager = new EavSpecificationManager(EavObject::getInstanceByIdentifier($method));
			$eavManager->setFormResponse($response, $forms[$method]);
			foreach (array('groupClass', 'specFieldList') as $vars)
			{
				$offlineVars[$method][$vars] = $response->get($vars);
			}
		}

		$this->set('offlineVars', $offlineVars);

		return $forms;
	}

	private function getOfflinePaymentValidator($method)
	{
		$validator = $this->getValidator($method, $this->request);
		$eavManager = new EavSpecificationManager(EavObject::getInstanceByIdentifier($method));
		$eavManager->setValidation($validator);
		return $validator;
	}

	/**
	 *	@role login
	 */
	public function payCreditCardAction()
	{
		if ($id = $this->request->get('id'))
		{
			$this->request->set('order', $id);
		}

		$order = $this->getPaymentOrder();

		if ($redirect = $this->validateOrder($order, self::STEP_PAYMENT))
		{
			return $redirect;
		}

		// already paid?
		if ($order->isPaid)
		{
			return new ActionRedirectResponse('checkout', 'completed');
		}

		ActiveRecordModel::beginTransaction();

		$this->order->setCheckoutStep(CustomerOrder::CHECKOUT_PAY);
		$this->order->setPaymentMethod($this->config->get('CC_HANDLER'));

		// process payment
		$transaction = $this->getTransaction();
		$names = explode(' ', $this->request->get('ccName'), 2);
		$transaction->firstName->set(array_shift($names));
		$transaction->lastName->set(array_shift($names));

		$handler = $this->application->getCreditCardHandler($transaction);

		if ($this->request->isValueSet('ccType'))
		{
			$handler->setCardType($this->request->get('ccType'));
		}

		$handler->setCardData($this->request->get('ccNum'), $this->request->get('ccExpiryMonth'), $this->request->get('ccExpiryYear'), $this->request->get('ccCVV'));

		if (!$this->buildCreditCardValidator($handler)->isValid())
		{
			ActiveRecordModel::rollback();
			return $this->getPaymentPageRedirect();
		}

		if ($this->config->get('CC_AUTHONLY'))
		{
			$result = $handler->authorize();
		}
		else
		{
			$result = $handler->authorizeAndCapture();
		}

		if ($result instanceof TransactionResult)
		{
			$response = $this->registerPayment($result, $handler);

			$trans = $this->order->getTransactions()->get(0);

			$eavObject = EavObject::getInstance($trans);
			$eavObject->setStringIdentifier('creditcard');
			$eavObject->save();

			$trans->getSpecification()->loadRequestData($this->request);
			$trans->save();
		}
		elseif ($result instanceof TransactionError)
		{
			// set error message for credit card form
			$validator = $this->buildCreditCardValidator($handler);
			$validator->triggerError('creditCardError', $this->translate('_err_processing_cc'));
			$validator->saveState();

			$response = $this->getPaymentPageRedirect();
		}
		else
		{
			var_dump($result);
			throw new Exception('Unknown transaction result type: ' . get_class($result));
		}

		ActiveRecordModel::commit();

	}

	private function getPaymentPageRedirect()
	{
		if (is_numeric($this->request->get('id')))
		{
			return new ActionRedirectResponse('user', 'pay', array('id' => $this->request->get('id')));
		}
		else
		{
			return new ActionRedirectResponse('checkout', 'pay');
		}
	}

	/**
	 *	@role login
	 */
	public function payOfflineAction()
	{
		ActiveRecordModel::beginTransaction();

		$method = $this->request->get('id');

		if (!OfflineTransactionHandler::isMethodEnabled($method) || !$this->getOfflinePaymentValidator($method)->isValid())
		{
			return new ActionRedirectResponse('checkout', 'pay');
		}

		$order = $this->order;
		$this->order->setPaymentMethod($method);
		$response = $this->finalizeOrder();

		$transaction = Transaction::getNewOfflineTransactionInstance($order, 0);
		$transaction->setOfflineHandler($method);
		$transaction->save();

		$eavObject = EavObject::getInstance($transaction);
		$eavObject->setStringIdentifier($method);
		$eavObject->save();

		$transaction->getSpecification()->loadRequestData($this->request);
		$transaction->save();

		ActiveRecordModel::commit();

	}

	/**
	 *	@role login
	 */
	public function payExpressAction()
	{
		$res = $this->validateExpressCheckout();
		if ($res instanceof Response)
		{
			return $res;
		}

		$response = new ActionResponse;
		$this->set('order', $this->order->toArray());
		$this->set('currency', $this->getRequestCurrency());
		$this->set('method', $res->toArray());
	}

	/**
	 *	@role login
	 */
	public function payExpressCompleteAction()
	{
		$res = $this->validateExpressCheckout();
		if ($res instanceof Response)
		{
			return $res;
		}

		$transaction = $this->getTransaction();
		$handler = $res->getHandler($transaction);

		if ($handler->getConfigValue('AUTHONLY'))
		{
			$result = $handler->authorize();
		}
		else
		{
			$result = $handler->authorizeAndCapture();
		}

		if ($transaction->recurringItemCount)
		{
			$handler->createRecurringPaymentProfile();
		}

		if ($result instanceof TransactionResult)
		{
			return $this->registerPayment($result, $handler);
		}
		elseif ($result instanceof TransactionError)
		{
			ExpressCheckout::deleteInstancesByOrder($this->order);
			return $this->getPaymentPageRedirect();

			// set error message for credit card form

			// buildCreditCardValidator() need argument now
			// Do payExpressComplete() have credit card data?
			$validator = $this->buildCreditCardValidator();
			$validator->triggerError('creditCardError', $result->getMessage());
			$validator->saveState();

			return $this->getPaymentPageRedirect();
		}
		else
		{
			throw new Exception('Unknown transaction result type: ' . get_class($result));
		}
	}

	/**
	 *  Redirect to a 3rd party payment processor website to complete the payment
	 *  (Paypal IPN, 2Checkout, Moneybookers, etc)
	 *
	 *	@role login
	 */
	public function redirectAction()
	{
		$order = $this->getPaymentOrder();
		if ($redirect = $this->validateOrder($order, self::STEP_PAYMENT))
		{
			return $redirect;
		}

		$notifyParams = $this->request->isValueSet('order') ? array('order' => $this->request->get('order')) : array();

		$class = $this->request->get('id');
		$order->setPaymentMethod($class);
		$this->order = $order;

		$handler = $this->application->getPaymentHandler($class, $this->getTransaction());
		$handler->setNotifyUrl($this->router->createFullUrl($this->url->get('checkout/notify', 'id' => $class, 'query' => $notifyParams))));
		$handler->setReturnUrl($this->router->createFullUrl($this->url->get('checkout/completeExternal', 'id' => $order->getID()))));
		$handler->setCancelUrl($this->router->createFullUrl($this->url->get('checkout/pay')));
		$handler->setSiteUrl($this->router->createFullUrl($this->url->get('index/index')));

		// transaction information is not return back online, so the order is finalized right away
		if (!$handler->isNotify())
		{
			$this->finalizeOrder();
		}

		$this->order->setCheckoutStep(CustomerOrder::CHECKOUT_PAY);

		if ($handler->isPostRedirect())
		{

			$this->set('url', $handler->getUrl());
			$this->set('params', $handler->getPostParams());
		}

		return new RedirectResponse($handler->getUrl());
	}

	/**
	 *  Payment confirmation post-back URL for 3rd party payment processors
	 *  (Paypal IPN, 2Checkout, Moneybookers, etc)
	 */
	public function notifyAction()
	{
		$handler = $this->application->getPaymentHandler($this->request->get('id'));
		$orderId = $handler->getOrderIdFromRequest($this->request->toArray());

		if (!$this->getRequestCurrency())
		{
			$this->request->set('currency', $handler->getCurrency($this->request->get('currency')));
		}

		$order = CustomerOrder::getInstanceById($orderId, CustomerOrder::LOAD_DATA);
		$order->setPaymentMethod(get_class($handler));
		$order->loadAll();
		$this->order = $order;
		$handler->setDetails($this->getTransaction());

		$result = $handler->notify($this->request->toArray());

		if ($result instanceof TransactionResult)
		{
			$this->registerPayment($result, $handler);
		}
		else
		{
			// set error message for credit card form
			$validator = $this->buildCreditCardValidator();
			$validator->triggerError('creditCardError', $result->getMessage());
			$validator->saveState();

			return $this->getPaymentPageRedirect();
		}

		// determine if the notification URL is called by payment gateway or the customer himself
		// this shouldn't usually happen though as the payment notifications should be sent by gateway
		if (($order->user == $this->user))
		{
			$this->request->set('id', $this->order->getID());
			return $this->completeExternal();
		}

		// some payment gateways (2Checkout, for example) require to return HTML response
		// to be displayed after the payment. In this case we're doing meta-redirect to get back to our site.
		else if ($handler->isHtmlResponse())
		{
			$returnUrl = $handler->getReturnUrlFromRequest($this->request->toArray());
			if (!$returnUrl)
			{
				$returnUrl = $this->url->get('checkout/completed', 'query' => array('id' => $this->order->getID())));
				$returnUrl = $this->router->createFullUrl($returnUrl);
			}

			$this->set('order', $order->toArray());
			$this->set('returnUrl', $returnUrl);
		}
	}

	/**
	 *	@role login
	 */
	public function completeExternalAction()
	{
		if (SessionOrder::getOrder()->getID() != $this->request->get('id'))
		{
			SessionOrder::destroy();
		}

		$order = CustomerOrder::getInstanceById($this->request->get('id'), CustomerOrder::LOAD_DATA);
		if ($order->user != $this->user)
		{
			throw new ApplicationException('Invalid order');
		}

		$this->session->set('completedOrderID', $order->getID());
		return new ActionRedirectResponse('checkout', 'completed');
	}

	/**
	 *	@role login
	 */
	public function completedAction()
	{
		if ($this->request->isValueSet('id'))
		{
			return new ActionRedirectResponse('checkout', 'completeExternal', array('id' => $this->request->get('id')));
		}

		$order = CustomerOrder::getInstanceByID((int)$this->session->get('completedOrderID'), CustomerOrder::LOAD_DATA);
		$order->loadAll();

		$this->set('order', $order->toArray());
		$this->set('url', $this->url->get('user/viewOrder', 'id' => $this->session->get('completedOrderID')), true));

		if (!$order->isPaid)
		{
			$transactions = $order->getTransactions()->toArray();
			$this->set('transactions', $transactions);
		}
		else
		{
			$this->set('files', ProductFile::getOrderFiles(select(eq('CustomerOrder.ID', $order->getID()))));
		}

	}

	public function cvvAction()
	{
		$this->addBreadCrumb($this->translate('_cvv'), '');


	}

	private function createAddress($addressClass, $prefix)
	{
		$address = UserAddress::getNewInstance();
		$this->saveAddress($address, $prefix);

		$addressType = call_user_func_array(array($addressClass, 'getNewInstance'), array($this->user, $address));

		if (!$this->user->isAnonymous())
		{
			$addressType->save();
		}

		return $addressType;
	}

	private function getPaymentOrder()
	{
		if (!$this->paymentOrder)
		{
			if ($this->request->get('order'))
			{
				$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'ID'), $this->request->get('order')));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
				//$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isPaid'), false));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), 0));

				$s = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
				if ($s->size())
				{
					$order = $s->get(0);
					$order->loadAll();
					$this->paymentOrder = $this->order = $order;
				}
			}
			else
			{
				$this->paymentOrder = $this->order;
			}
		}

		return $this->paymentOrder;
	}

	protected function registerPayment(TransactionResult $result, TransactionPayment $handler)
	{
		// transaction already registered?
		if (Transaction::getInstance($this->order, $result->gatewayTransactionID))
		{
			$this->session->set('completedOrderID', $this->order->getID());
			return new ActionRedirectResponse('checkout', 'completed');
		}

		$transaction = Transaction::getNewInstance($this->order, $result);
		$transaction->setHandler($handler);
		$transaction->save();

		$this->order->setPaidStatus();
		if ($this->order->isPaid)
		{
			$this->order->save();
		}

		return $this->finalizeOrder();
	}

	protected function finalizeOrder($options = array())
	{
		if (!count($this->order->getShipments()))
		{
			throw new ApplicationException('No shipments in order');
		}

		$user = $this->order->user;
		$user->load();
		$newOrder = $this->order->finalize($options);

		$orderArray = $this->order->toArray(array('payments' => true));

		// send order confirmation email
		if ($this->config->get('EMAIL_NEW_ORDER'))
		{
			$email = new Email($this->application);
			$email->setUser($user);
			$email->setTemplate('order.new');
			$email->set('order', $orderArray);
			$email->send();
		}

		// notify store admin
		if ($this->config->get('NOTIFY_NEW_ORDER'))
		{
			$email = new Email($this->application);
			$email->setTo($this->config->get('NOTIFICATION_EMAIL'), $this->config->get('STORE_NAME'));
			$email->setTemplate('notify.order');
			$email->set('order', $orderArray);
			$email->set('user', $user->toArray());
			$email->send();
		}

		$this->session->set('completedOrderID', $this->order->getID());

		if($newOrder instanceof CustomerOrder) // if user hasn't wish list items order->finalize() will return null, saving null with SessionOrder causes fatal error!
		{
			SessionOrder::save($newOrder);
		}
		else
		{
			SessionOrder::destroy();
		}

		return new ActionRedirectResponse('checkout', 'completed');
	}

	private function getTransaction()
	{
		$this->order->loadAll();
		$this->order->getTotal(true);
		return new LiveCartTransaction($this->order, Currency::getValidInstanceById($this->getRequestCurrency()));
	}

	/******************************* VALIDATION **********************************/

	/**
	 *	Determines if the necessary steps have been completed, so the order could be finalized
	 *
	 *	@return RedirectResponse
	 *	@return ActionRedirectResponse
	 *	@return false
	 */
	protected function validateOrder(CustomerOrder $order, $step = 0)
	{
		if ($order->isFinalized)
		{
			return false;
		}

		// no items in shopping cart
		if (!count($order->getShoppingCartItems()))
		{
			if ($this->request->isValueSet('return'))
			{
				return new RedirectResponse($this->router->createUrlFromRoute($this->request->get('return')));
			}
			else
			{
				return new ActionRedirectResponse('index', 'index');
			}
		}

		// order is not orderable (too few/many items, etc.)
		$isOrderable = $order->isOrderable(true, false);
		if (!$isOrderable || $isOrderable instanceof OrderException)
		{
			return new ActionRedirectResponse('order', 'index');
		}

		$valStep = $this->config->get('CHECKOUT_CUSTOM_FIELDS');
		$validateFields = ('CART_PAGE' == $valStep) ||
						  (('BILLING_ADDRESS_STEP' == $valStep) && (self::STEP_ADDRESS <= $step)) ||
						  (('SHIPPING_ADDRESS_STEP' == $valStep) && (((self::STEP_ADDRESS == $step) && ('shipping' == $this->request->get('step'))) || (self::STEP_ADDRESS < $step))) ||
						  (('SHIPPING_METHOD_STEP' == $valStep) && (self::STEP_SHIPPING < $step));

		$isOrderable = $order->isOrderable(true, $validateFields);

		// custom fields selected in cart page?
		if (('CART_PAGE' == $valStep) && !$isOrderable)
		{
			return new ActionRedirectResponse('order', 'index');
		}

		// shipping address selected
		if ($step >= self::STEP_SHIPPING)
		{
			if ((!$order->shippingAddress && $order->isShippingRequired() && !$order->isMultiAddress) || !$order->billingAddress || !$isOrderable)
			{
				return new ActionRedirectResponse('checkout', 'selectAddress', $this->request->get('step') ? array('step' => $this->request->get('step')) : null);
			}
		}

		// shipping method selected
		if (($step >= self::STEP_PAYMENT && $order->isShippingRequired()) || !$isOrderable)
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

	private function validateExpressCheckout()
	{
		if ($redirect = $this->validateOrder($this->order, self::STEP_PAYMENT))
		{
			return $redirect;
		}

		$expressInstance = ExpressCheckout::getInstanceByOrder($this->order);

		if (!$expressInstance)
		{
			return new ActionRedirectResponse('order', 'index');
		}

		$this->order->setPaymentMethod(get_class($expressInstance));

		try
		{
			$handler = $expressInstance->getTransactionDetails($this->getTransaction());
		}
		catch (PaymentException $e)
		{
			$expressInstance->delete();
			return new ActionRedirectResponse('checkout', 'express', array('id' => $expressInstance->method));
		}

		return $expressInstance;
	}

	private function buildShippingForm(/*ARSet */$shipments)
	{
		return new Form($this->buildShippingValidator($shipments));
	}

	private function buildShippingValidator(/*ARSet */$shipments)
	{
		$validator = $this->getValidator("shipping", $this->request);
		foreach ($shipments as $key => $shipment)
		{
			if ($shipment->isShippable())
			{
				$validator->add('shipping_' . $key, new Validator\PresenceOf(array('message' => $this->translate('_err_select_shipping'))));
			}
		}

		if ($this->config->get('CHECKOUT_CUSTOM_FIELDS') == 'SHIPPING_METHOD_STEP')
		{
			$shipment->order->getSpecification()->setValidation($validator);
		}

		return $validator;
	}

	private function buildAddressSelectorForm(CustomerOrder $order, $step)
	{
		$validator = $this->buildAddressSelectorValidator($order, $step);

		$form = new Form($validator);
		$form->set('billing_country', $this->config->get('DEF_COUNTRY'));
		$form->set('shipping_country', $this->config->get('DEF_COUNTRY'));

		return $form;
	}

	private function buildAddressSelectorValidator(CustomerOrder $order, $step = null)
	{
		$this->loadLanguageFile('User');

		$validator = $this->getValidator("addressSelectorValidator", $this->request);

		if (!$step || ('billing' == $step))
		{
			$validator->add('billingAddress', new OrCheck(array('billingAddress', 'billing_address1'), array(new Validator\PresenceOf(array('message' => $this->translate('_select_billing_address')), new Validator\PresenceOf()), $this->request)));
			$this->validateAddress($validator, 'billing_');
		}

		if (!$step || ('shipping' == $step))
		{
			if ($this->isShippingRequired($order) && !$order->isMultiAddress)
			{
				$validator->add('shippingAddress', new OrCheck(array('shippingAddress', 'sameAsBilling', 'shipping_address1'), array(new Validator\PresenceOf(array('message' => $this->translate('_select_shipping_address')), new Validator\PresenceOf(), new Validator\PresenceOf()), $this->request)));
				$this->validateAddress($validator, 'shipping_');
			}
		}

		$fieldStep = $this->config->get('CHECKOUT_CUSTOM_FIELDS');
		if ((($fieldStep == 'BILLING_ADDRESS_STEP') && (('billing' == $step) || !$step || !$this->isShippingRequired($order))) ||
		   (($fieldStep == 'SHIPPING_ADDRESS_STEP') && (('shipping' == $step))))
		{
			$order->getSpecification()->setValidation($validator);
		}

		return $validator;
	}

	protected function isShippingRequired(CustomerOrder $order)
	{
		return !$this->config->get('REQUIRE_SAME_ADDRESS') && $this->order->isShippingRequired();
	}

	protected function validateAddress(\Phalcon\Validation $validator, $prefix)
	{
		$someValidator = $this->getValidator('foo', $this->request);
				$con = new UserController($this->application);
		$con->validateAddress($someValidator, $prefix, 'shipping_' == $prefix);

		foreach ($someValidator->getValidatorVars() as $field => $var)
		{
			foreach ($var->getChecks() as $check)
			{
				$validator->add($field, new OrCheck(array($field, substr($prefix, 0, -1) . 'Address'), array($check, new Validator\PresenceOf()), $this->request));
			}
		}
	}

	public function buildCreditCardFormAction(CreditCardPayment $ccHandler)
	{
		$form = new Form($this->buildCreditCardValidator($ccHandler));
		$form->set('ccExpiryMonth', date('n'));
		$form->set('ccExpiryYear', date('Y'));
		return $form;
	}

	private function buildCreditCardValidator(CreditCardPayment $ccHandler = null)
	{
		$validator = $this->getValidator("creditCard", $this->request);
		$validator->add('ccName', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_cc_name'))));
		$validator->add('ccNum', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_cc_num'))));
		$validator->add('ccExpiryMonth', new Validator\PresenceOf(array('message' => $this->translate('_err_select_cc_expiry_month'))));
		$validator->add('ccExpiryYear', new Validator\PresenceOf(array('message' => $this->translate('_err_select_cc_expiry_year'))));

		if ($ccHandler)
		{
			if ($ccHandler->isCardTypeNeeded())
			{
				$validator->add('ccType', new Validator\PresenceOf(array('message' => $this->translate('_err_select_cc_type'))));
			}

			if ($this->config->get('REQUIRE_CVV') && $ccHandler->isCvvRequired())
			{
				$validator->add('ccCVV', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_cc_cvv'))));
			}
		}

		$validator->addFilter('ccCVV', new RegexFilter('[^0-9]'));
		$validator->addFilter('ccNum', new RegexFilter('[^ 0-9]'));

		$eavManager = new EavSpecificationManager(EavObject::getInstanceByIdentifier('creditcard'));
		$eavManager->setValidation($validator);

		return $validator;
	}
}


class CheckoutBillingAddressCheckCondition extends CheckCondition
{
	function isSatisfied()
	{
		return !$this->request->get('billingAddress');
	}
}

class CheckoutShippingAddressCheckCondition extends CheckCondition
{
	function isSatisfied()
	{
		return  !$this->request->get('shippingAddress') &&
				!$this->request->get('sameAsBilling');
	}
}

?>
