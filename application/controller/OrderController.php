<?php

use \product\Product;
use \order\CustomerOrder;
use \order\OrderedItem;

/**
 * @author Integry Systems
 * @package application/controller
 */
class OrderController extends FrontendController
{
	/**
	 *  View shopping cart contents
	 */
	public function indexAction()
	{
		if ($this->order->isMultiAddress)
		{
			return $this->response->redirect('order/multi');
		}

		if (!$this->user->isAnonymous())
		{
			if (!$this->order->user || ($this->order->user->getID() != $this->user->getID()))
			{
				$this->order->setUser($this->user);
				$this->order->save();
			}
		}
		else if ($this->config->get('DISABLE_GUEST_CART'))
		{
			return new ActionRedirectResponse('user', 'login', array('returnPath' => true));
		}

		$this->order->getTotal(true);

		$response = $this->getCartPageResponse();

		$this->addBreadCrumb($this->translate('_my_basket'), '');
	}

	/**
	 *  View shopping cart contents
	 */
	public function cartPopupAction()
	{
		if (!$this->user->isAnonymous())
		{
			if (!$this->order->user || ($this->order->user->getID() != $this->user->getID()))
			{
				$this->order->setUser($this->user);
				$this->order->save();
			}
		}
		else if ($this->config->get('DISABLE_GUEST_CART'))
		{
			return new ActionRedirectResponse('user', 'login', array('returnPath' => true));
		}

		$this->order->getTotal(true);

		return $this->getCartPageResponse();
	}


	/**
	 *  View shopping cart contents
	 */
	public function addConfirmationAction()
	{
		if (!$this->user->isAnonymous())
		{
			if (!$this->order->user || ($this->order->user->getID() != $this->user->getID()))
			{
				$this->order->setUser($this->user);
				$this->order->save();
			}
		}
		else if ($this->config->get('DISABLE_GUEST_CART'))
		{
			return new ActionRedirectResponse('user', 'login', array('returnPath' => true));
		}

		$response = new BlockResponse();
		$this->set('msg', $this->request->get('message'));
		$this->set('error', $this->request->get('err'));
		$this->set('cart', $this->order->toArray());
	}

	/**
	 *	@role login
	 */
	public function multiAction()
	{
		if (!$this->order->isMultiAddress)
		{
			return $this->response->redirect('order/index');
		}

		$response = $this->getCartPageResponse();

		// we're loading through a set, because all referenced records need to be loaded before array transformation
		$addresses = array();
		foreach ($this->user->getShippingAddressSet()->toArray() as $address)
		{
			$addresses[$address['UserAddress']['ID']] = $address['UserAddress']['compact'];
		}
		foreach ($this->user->getBillingAddressSet()->toArray() as $address)
		{
			$addresses[$address['UserAddress']['ID']] = $address['UserAddress']['compact'];
		}

		$this->set('addresses', $addresses);

		if (!$addresses)
		{
			return new ActionRedirectResponse('user', 'addShippingAddress', array('returnPath' => true));
		}

		$this->addBreadCrumb($this->translate('_select_shipping_addresses'), '');
	}

	private function getCartPageResponse()
	{
		$this->addBreadCrumb($this->translate('_my_session'), $this->router->createUrlFromRoute($this->request->get('return'), true));

		$this->order->setUser($this->user);
		$this->order->loadItemData();


		if ($result = $this->order->updateToStock())
		{
			$this->set('changes', $result);
		}

		$options = $this->getItemOptions();
		$currency = Currency::getValidInstanceByID($this->request->get('currency', $this->application->getDefaultCurrencyCode()), Currency::LOAD_DATA);
		$form = $this->buildCartForm($this->order, $options);

		if ($this->isTosInCartPage())
		{
			$form->set('tos', $this->session->get('tos'));
		}

		if ($this->config->get('ENABLE_SHIPPING_ESTIMATE'))
		{
			$this->loadLanguageFile('User');

			if ($this->estimateShippingCost())
			{
				$this->order->getTotal(true);
				$this->set('isShippingEstimated', true);
			}

			$address = $this->order->shippingAddress;
			foreach (array('countryID' => 'country', 'stateName' => 'state_text', 'postalCode' => 'postalCode', 'city' => 'city') as $addressKey => $formKey)
			{
				$form->set('estimate_' . $formKey, $address->$addressKey);
			}

			if ($address->state && $address->state->getID())
			{
				$form->set('estimate_state_select', $address->state->getID());
			}

			$this->set('countries', $this->getCountryList($form));
			$this->set('states', $this->getStateList($form->get('estimate_country')));

			$hideConf = (array)$this->config->get('SHIP_ESTIMATE_HIDE_ENTRY');
			$hideForm = (!empty($hideConf['UNREGISTERED']) && $this->user->isAnonymous()) ||
						(!empty($hideConf['ALL_REGISTERED']) && !$this->user->isAnonymous()) ||
						(!empty($hideConf['REGISTERED_WITH_ADDRESS']) && !$this->user->isAnonymous() && !$this->user->defaultBillingAddress) ||
						!$this->order->isShippingRequired() ||
						$this->order->isMultiAddress;

			$this->set('hideShippingEstimationForm', $hideForm);
		}

		$orderArray = $this->order->toArray();

		$itemsById = array();
		$recurringItemsByItem = array();
		$hasRecurringItem = false;
		foreach (array('cartItems', 'wishListItems') as $type)
		{
			if (!empty($orderArray[$type]))
			{
				foreach ($orderArray[$type] as &$item)
				{
					$itemsById[$item['ID']] =& $item;
					if ($item['Product']['type'] == Product::TYPE_RECURRING)
					{
						$hasRecurringItem = true;
						$recurringProductPeriods = RecurringProductPeriod::getRecordSetByProduct($item['Product']['ID'])->toArray();
						$recurringItemsByItem[$item['ID']] = $recurringProductPeriods;
					}
				}
			}
		}
		if ($hasRecurringItem)
		{
			$this->set('periodTypesPlural', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL));
			$this->set('periodTypesSingle', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_SINGLE));
			$this->set('recurringItemsByItem', $recurringItemsByItem);
		}
		$this->set('cart', $orderArray);
		$this->set('itemsById', $itemsById);
		$this->set('form', $form);
		$this->set('return', $this->request->get('return'));
		$this->set('currency', $currency->getID());
		$this->set('options', $options['visible']);
		$this->set('moreOptions', $options['more']);
		$this->set('orderTotal', $currency->getFormattedPrice($this->order->getTotal()));
		$this->set('expressMethods', $this->application->getExpressPaymentHandlerList(true));
		$this->set('isCouponCodes', DiscountCondition::isCouponCodes());
		$this->set('isOnePageCheckout', ($this->config->get('CHECKOUT_METHOD') == 'CHECKOUT_ONEPAGE') && !$this->order->isMultiAddress && !$this->session->get('noJS'));

		$this->order->getSpecification()->setFormResponse($response, $form);

		SessionOrder::getorderBy()->getShoppingCartItems();

	}

	private function estimateShippingCost()
	{
		if (!$this->config->get('ENABLE_SHIPPING_ESTIMATE'))
		{
			return false;
		}

		$estimateAddress = SessionOrder::getEstimateAddress();
		$this->order->shippingAddress->set($estimateAddress);

		$isShippingEstimated = false;
		foreach ($this->order->getShipments() as $shipment)
		{
			if (!$shipment->getSelectedRate())
			{
				$cheapest = $cheapestRate = null;
				$rates = $shipment->getShippingRates();
				foreach ($rates as $rate)
				{
					$price = $rate->getAmountByCurrency($this->order->getCurrency());
					if (!$cheapestRate || ($price < $cheapest))
					{
						$cheapestRate = $rate;
						$cheapest = $price;
					}
				}

				if ($cheapestRate)
				{
					$shipment->setRateId($cheapestRate->getServiceID());
					$isShippingEstimated = true;
				}
			}
		}

		return $isShippingEstimated;
	}

	private function getItemOptions()
	{
		// load product options
		$products = array();
		foreach ($this->order->getOrderedItems() as $item)
		{
			$products[$item->getProduct()->getID()] = $item->getProduct();
		}

		$options = ProductOption::loadOptionsForProductSet(ARSet::buildFromArray($products));

		$moreOptions = $optionsArray = array();
		foreach ($this->order->getOrderedItems() as $item)
		{
			$productID = $item->getProduct()->getID();
			if (isset($options[$productID]))
			{
				$optionsArray[$item->getID()] = $this->getOptionsArray($options[$productID], $item, 'isDisplayedInCart');
				$moreOptions[$item->getID()] = $this->getOptionsArray($options[$productID], $item, 'isDisplayed');
			}
		}

		// are there any options that are available for customer to set, but not displayed right away?
		foreach ($moreOptions as &$options)
		{
			foreach ($options as $key => $option)
			{
				if ($option['isDisplayedInCart'])
				{
					unset($options[$key]);
				}
			}
		}

		return array('visible' => $optionsArray, 'more' => $moreOptions);
	}

	public function optionsAction()
	{
		$response = $this->index();
		$this->set('editOption', $this->request->get('id'));
	}

	public function optionFormAction(CustomerOrder $order = null, $filter = 'isDisplayed')
	{
		$order = $order ? $order : $this->order;

		$item = $order->getItemByID($this->request->get('id'));
		$options = $optionsArray = array();
		$product = $item->getProduct();
		$options[$product->getID()] = $product->getOptions(true);
		$optionsArray[$item->getID()] = $this->getOptionsArray($options[$product->getID()], $item, $filter);

		$this->setLayout('empty');


		$this->set('form', $this->buildOptionsForm($item, $options));
		$this->set('options', $optionsArray);
		$this->set('item', $item->toArray());
	}

	public function variationFormAction(CustomerOrder $order = null)
	{
		$order = $order ? $order : $this->order;

		$item = $order->getItemByID($this->request->get('id'));
		$variations = $item->getProduct()->getVariationData($this->application);

		$this->setLayout('empty');


		$this->set('form', $this->buildVariationsForm($item, $variations));
		$this->set('variations', $variations);
		$this->set('item', $item->toArray());
	}

	private function getOptionsArray($set, $item, $filter = 'isDisplayed')
	{
		$out = array();
		foreach ($set as $option)
		{
			$arr = $option->toArray();
			$arr['fieldName'] = $this->getFormFieldName($item, $option);

			$invalid = !empty($_SESSION['optionError'][$item->getID()][$option->getID()]) && ('isDisplayedInCart' == $filter);

			if (!$filter || $option->$filter || $invalid)
			{
				$out[] = $arr;
			}
		}

		return $out;
	}

	/**
	 *  Update product quantities
	 */
	public function updateAction()
	{
		// TOS
		if ($this->isTosInCartPage())
		{
			$this->session->set('tos', $this->request->get('tos'));
		}

		// coupon code
		if ($this->request->get('coupon'))
		{
			$code = $this->request->get('coupon');

			if ($condition = DiscountCondition::getInstanceByCoupon($code))
			{
				if (!$this->order->hasCoupon($code))
				{
					$coupon = OrderCoupon::getNewInstance($this->order, $code);
					$coupon->save();

					$this->order->getCoupons(true);

					if ($this->order->hasCoupon($code))
					{
						$this->setMessage($this->makeText('_coupon_added', array($code)));
					}
				}
			}
			else
			{
				$this->setErrorMessage($this->makeText('_coupon_not_found', array($code)));
			}

			$this->order->getCoupons(true);
		}

		$this->updateEstimateAddress();

		$this->order->loadItemData();
		$validator = $this->buildCartValidator($this->order, $this->getItemOptions());

		if (!$validator->isValid())
		{
			return $this->response->redirect('order/index');
		}

		$this->order->loadRequestData($this->request);

		foreach ($this->order->getOrderedItems() as $item)
		{
			if ($this->request->has('item_' . $item->getID()))
			{
				foreach ($item->getProduct()->getOptions(true) as $option)
				{
					$this->modifyItemOption($item, $option, $this->request, $this->getFormFieldName($item, $option));
				}

				$item->save();

				$this->order->updateCount($item, $this->request->get('item_' . $item->getID(), 0));
			}
		}

		if ($this->order->isMultiAddress)
		{
			$addresses = $this->user->getShippingAddressSet();
			$this->order->getShipments();

			foreach ($this->order->getOrderedItems() as $item)
			{
				if ($addressId = $this->request->get('address_' . $item->getID()))
				{
					if (!$item->shipment || !$item->shipment->shippingAddress || ($item->shipment->shippingAddress->getID() != $addressId))
					{
						foreach ($this->order->getShipments() as $shipment)
						{
							if ($shipment->shippingAddress && ($shipment->shippingAddress->getID() == $addressId))
							{
								if (!$item->shipment || ($item->shipment->getID() != $shipment->getID()))
								{
									if ($item->shipment)
									{
										$item->shipment->removeItem($item);
									}

									$shipment->addItem($item);
									break;
								}
							}

							$shipment = null;
						}

						if (!isset($shipment) || !$shipment)
						{
							$address = UserAddress::getInstanceByID($addressId, true);

							$shipment = Shipment::getNewInstance($this->order);
							$shipment->shippingAddress->set($address);
							$shipment->save();
							$this->order->addShipment($shipment);

							$shipment->addItem($item);
						}

						$item->save();
					}
				}

				if ($item->shipment)
				{
					$item->shipment->shippingAmount->set(0);
					$item->shipment->shippingServiceData->set(null);
					$item->shipment->save();
				}
			}
		}

		$this->order->mergeItems();

		SessionOrder::save($this->order);

		// proceed with the checkout
		if ($this->request->get('proceed'))
		{
			return $this->response->redirect('checkout/index');
		}

		// redirect to payment gateway
		if ($url = $this->request->get('redirect'))
		{
			return new RedirectResponse($url);
		}

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	private function updateEstimateAddress()
	{
		if ($this->config->get('ENABLE_SHIPPING_ESTIMATE'))
		{
			if ($this->request->get('estimate_state_select'))
			{
				$this->request->set('estimate_stateID', $this->request->get('estimate_state_select'));
			}

			if ($this->request->get('estimate_state_text'))
			{
				$this->request->set('estimate_stateName', $this->request->get('estimate_state_text'));
			}

			$address = SessionOrder::getDefaultEstimateAddress();
			$address->loadRequestData($this->request, 'estimate_');

			if ($country = $this->request->get('estimate_country'))
			{
				$address->countryID->set($country);
			}

			SessionOrder::setEstimateAddress($address);
		}
	}

	/**
	 *  Remove a product from shopping cart
	 */
	public function deleteAction()
	{
		$item = OrderedItem::getInstanceByID($this->request->get('id'), ActiveRecordModel::LOAD_DATA, array('Product'));
		$this->setMessage($this->makeText('_removed_from_cart', array($item->getProduct()->getName($this->getRequestLanguage()))));
		$this->order->removeItem($item);
		SessionOrder::save($this->order);

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Add a new product to shopping cart
	 */
	public function addToCartAction()
	{
		// avoid search engines adding items to cart...
		if ($this->request->get('csid') && ($this->request->get('csid') != session_id()))
		{
			return;
		}

		if (!$this->request->get('count'))
		{
			$this->request->set('count', 1);
		}

		if ($id = $this->request->get('id'))
		{
			$res = $this->addProductToCart($id);

			if ($res instanceof ActionRedirectResponse)
			{
				if ($this->isAjax())
				{
					return new JSONResponse(array('__redirect' => $this->application->getActionRedirectResponseUrl($res)));
				}
				else
				{
					return $res;
				}
			}

			if ($res->count < $this->request->get('count'))
			{
				$this->setErrorMessage($this->makeText('_add_to_cart_quant_error', array(Product::getInstanceByID($id)->getName($this->getRequestLanguage()), $res->count, $this->request->get('count'))));
			}

			$this->setMessage($this->makeText('_added_to_cart', array(Product::getInstanceByID($id)->getName($this->getRequestLanguage()))));
		}

		if ($ids = $this->request->get('productIDs'))
		{
			$added = false;
			foreach ($ids as $id)
			{
				$res = $this->addProductToCart($id, 'product_' . $id . '_');

				if ($res instanceof ActionRedirectResponse)
				{
					//return $res;
				}
				else if ($res)
				{
					$added = true;
				}
			}

			if ($added)
			{
				$this->setMessage($this->translate('_selected_to_cart'));
			}
		}

		if (!$this->user->isAnonymous())
		{
			$this->order->setUser($this->user);
		}

		$this->order->mergeItems();
		SessionOrder::save($this->order);

		if (!$this->isAjax())
		{
			if ($this->config->get('SKIP_CART'))
			{
				return $this->response->redirect('checkout/index');
			}
			else
			{
				return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
			}
		}
		else
		{
			return $this->cartUpdate();
		}
	}

	public function cartUpdateAction()
	{
		if ($this->order->isMultiAddress)
		{
			return $this->response->redirect('order/multi');
		}
		else
		{
			$response = new CompositeJSONResponse();
			$response->addAction('miniCart', 'order', 'miniCartBlock');

			if ($this->config->get('POPUP_CART_TYPE') == 'FULL_CART')
			{
				$response->addAction('popupCart', 'order', 'cartPopup');
			}
			else
			{
				$response->addAction('popupCart', 'order', 'addConfirmation', array('message' => $this->getMessage(), 'err' => $this->getErrorMessage()));
			}

			return $this->ajaxResponse($response);
		}
	}

	public function miniCartBlockAction()
	{
		$this->loadLanguageFile('Order');
		$this->order->loadAll();
		$this->order->getTotal(true);
		return new BlockResponse('order', $this->order->toArray());
	}

	public function ajaxCartAction()
	{
		$this->loadLanguageFile('Order');
		$this->order->loadAll();
		$this->order->getTotal(true);
		return new BlockResponse('order', $this->order->toArray());
	}

	private function addProductToCart($id, $prefix = '')
	{
		if ($prefix && !$this->request->get($prefix . 'count'))
		{
			return '"';
		}

		$product = Product::getInstanceByID($id);
		$productRedirect = $this->response->redirect(route($product));
		//$productRedirect = new ActionRedirectResponse('product', 'index', array('id' => $product->getID(), 'query' => 'return=' . $this->request->get('return')));
		if (!$product->isAvailable())
		{
			$this->flashSession->error($this->translate('_product_unavailable'));
			return $productRedirect->send();
		}

		/*
		$variations = !$product->parent ? $product->getVariationData($this->application) : array();

		// add first variations by default?
		$autoVariation = false;
		if ($variations && $this->config->get('DEF_FIRST_VARIATION'))
		{
			$first = reset($variations['variations']);
			if (!$this->request->get($prefix . 'variation_' . $first['ID']))
			{
				$autoVariation = true;
			}
		}
		*/

		/*
		$validator = ProductController::buildAddToCartValidator($product->getOptions(true)->toArray(), $autoVariation ? array() : $variations, $prefix);
		if (!$validator->isValid())
		{
			return $productRedirect->send();
		}
		*/

		// check if a variation needs to be added to cart instead of a parent product
		/*
		if (!empty($variations))
		{
			if ($autoVariation)
			{
				$foundVariation = false;
				foreach ($variations['products'] as $prod)
				{
					if (Product::isOrderable($prod))
					{
						$product = Product::getInstanceByID($prod['ID'], true);
						$foundVariation = true;
						break;
					}
				}

				if (!$foundVariation)
				{
					return $productRedirect->send();
				}
			}
			else
			{
				$product = $this->getVariationFromRequest($variations);
			}
		}
		*/

		$count = $this->request->get($prefix . 'count', null, 1);
		if ($count < $product->getMinimumQuantity())
		{
			$count = $product->getMinimumQuantity();
		}

		$item = $this->order->addProduct($product, $count);
		if ($item instanceof OrderedItem)
		{
			$item->name->set($product->name);
			foreach ($product->getOptions(true) as $option)
			{
				$this->modifyItemOption($item, $option, $this->request, $prefix . 'option_' . $option->getID());
			}

			if ($this->order->isMultiAddress)
			{
				$item->save();
			}

			if ($product->type == Product::TYPE_RECURRING)
			{
				if ($item->isExistingRecord() == false)
				{
					$item->save(); // or save in SessionOrder::save()
				}
				$recurringID = $this->getRequest()->get('recurringID');

				$recurringProductPeriod = $product->getRecurringProductPeriodById($recurringID);
				if ($recurringProductPeriod == null)
				{
					$recurringProductPeriod = $product->getDefaultRecurringProductPeriod();
				}

				if ($recurringProductPeriod)
				{
					$instance = RecurringItem::getNewInstance($recurringProductPeriod, $item);
					$instance->save();

					$item->updateBasePriceToCalculatedPrice();
				}
				// what if product with type recurring but no plan? just ignore?
			}
		}

		$this->order->updateToStock(false);

		return $item;
	}

	public function moveToCartAction()
	{
		$item = $this->order->getItemByID($this->request->get('id'));
		$item->isSavedForLater->set(false);
		$this->order->mergeItems();
		$this->order->resetShipments();
		SessionOrder::save($this->order);

		$this->setMessage($this->makeText('_moved_to_cart', array($item->getProduct()->getName('name', $this->getRequestLanguage()))));

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	public function moveToWishListAction()
	{
		$item = $this->order->getItemByID($this->request->get('id'));
		$item->isSavedForLater->set(true);
		$this->order->mergeItems();
		$this->order->resetShipments();
		SessionOrder::save($this->order);

		$this->setMessage($this->makeText('_moved_to_wishlist', array($item->getProduct()->getName('name', $this->getRequestLanguage()))));

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Add a new product to wish list (save items for buying later)
	 */
	public function addToWishListAction()
	{
		// avoid search engines adding items to cart...
		if ($this->request->get('csid') && ($this->request->get('csid') != session_id()))
		{
			return new RawResponse();
		}

		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA);

		$this->order->addToWishList($product);
		$this->order->mergeItems();
		SessionOrder::save($this->order);

		$this->setMessage($this->makeText('_added_to_wishlist', array($product->getName($this->getRequestLanguage()))));

		if (!$this->isAjax())
		{
			return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
		}
		else
		{
			$response = new CompositeJSONResponse();
			return $this->ajaxResponse($response);
		}
	}

	public function modifyItemOptionAction(OrderedItem $item, ProductOption $option, Request $request, $varName)
	{
		if ($option->isBool() && $request->has('checkbox_' . $varName))
		{
			if ($request->get($varName))
			{
				$item->addOptionChoice($option->defaultChoice);
			}
			else
			{
				$item->removeOption($option);
			}
		}
		else if ($option->isFile())
		{
			if (isset($_FILES['upload_' . $varName]))
			{
				$file = $_FILES['upload_' . $varName];
				if (!empty($file['name']))
				{
					$item->removeOption($option);
					$choice = $item->addOptionChoice($option->defaultChoice);
					$choice->setFile($_FILES['upload_' . $varName]);
				}
			}
		}
		else if ($request->get($varName))
		{
			if ($option->isSelect())
			{
				$item->addOptionChoice($option->getChoiceByID($request->get($varName)));
			}
			else if ($option->isText())
			{
				$text = $request->get($varName);

				if ($text)
				{
					$choice = $item->addOptionChoice($option->defaultChoice);
					$choice->optionText->set($text);
				}
				else
				{
					$item->removeOption($option);
				}
			}
		}
	}

	/**
	 *	@role login
	 */
	public function setMultiAddressAction()
	{
		if (!$this->config->get('ENABLE_MULTIADDRESS'))
		{
			return $this->response->redirect('order/index');
		}

		$this->order->isMultiAddress->set(true);
		$this->order->shippingAddress->set(null);

		// split items
		foreach ($this->order->getOrderedItems() as $item)
		{
			if ($item->count > 1)
			{
				$count = $item->count;
				$item->count->set(1);
				for ($k = 1; $k < $count; $k++)
				{
					$this->order->addItem(clone $item);
				}
			}
		}

		$this->order->save();

		return $this->response->redirect('order/multi');
	}

	public function setSingleAddressAction()
	{
		$f = new ARUpdateFilter('OrderedItem.customerOrderID = :OrderedItem.customerOrderID:', array('OrderedItem.customerOrderID' => $this->order->getID()));
		$f->addModifier('OrderedItem.shipmentID', new ARExpressionHandle('NULL'));
		ActiveRecordModel::updateRecordSet('OrderedItem', $f);

		$this->order->isMultiAddress->set(false);
		$this->order->loadAll();
		$this->order->mergeItems();
		$this->order->resetShipments();

		SessionOrder::save($this->order);
		$this->order->deleteRelatedRecordSet('Shipment');
		return $this->response->redirect('order/index');
	}

	public function getVariationFromRequestAction(array $variations)
	{
		$ids = array();
		foreach ($variations['variations'] as $variation)
		{
			$ids[] = $this->request->get('variation_' . $variation['ID']);
		}

		$hash = implode('-', $ids);
		if (!isset($variations['products'][$hash]))
		{
			return $productRedirect;
		}

		return Product::getInstanceByID($variations['products'][$hash]['ID'], Product::LOAD_DATA);
	}

	public function downloadOptionFileAction()
	{

		$f = select(eq('CustomerOrder.userID', $this->user->getID()),
					eq('OrderedItem.ID', $this->request->get('id')),
					eq('ProductOptionChoice.optionID', $this->request->get('option')));

		$set = ActiveRecordModel::getRecordSet('OrderedItemOption', $f, array('CustomerOrder', 'OrderedItem', 'ProductOptionChoice'));
		if ($set->count())
		{
			return new ObjectFileResponse($set->shift()->getFile());
		}
	}

	public function uploadOptionFileAction()
	{

		$field = 'upload_' . $this->request->get('field');
		$option = ProductOption::getInstanceByID($this->request->get('id'), true);
		$validator = $this->getValidator('optionFile');
		$this->addOptionValidation($validator, $option->toArray(), $field);

		if (!$validator->isValid())
		{
			return new JSONResponse(array('error' => $validator->getErrorList()));
		}

		// create tmp file
		$file = $_FILES[$field];
		$tmp = 'tmp_' . $field . md5($file['tmp_name']) .  '__' . $file['name'];
		$dir = $this->config->getPath('public/upload/optionImage/');
		$path = $dir . $tmp;

		if (!file_exists($dir))
		{
			mkdir($dir);
			chmod($dir, 0777);
		}

		move_uploaded_file($file['tmp_name'], $path);

		// create thumbnail
		$thumb = null;
		if (@getimagesize($path))
		{
			$thumb = 'tmp_thumb_' . $tmp;
			$thumbPath = $dir . $thumb;
			OrderedItemOption::resizeImage($path, $thumbPath, 1);
		}

		return new JSONResponse(array('name' => $file['name'], 'file' => $tmp, 'thumb' => $thumb));
	}

	/**
	 *	@todo Optimize loading of product options
	 */
	private function buildCartForm(CustomerOrder $order, $options)
	{
		$form = new Form($this->buildCartValidator($order, $options));

		foreach ($order->getOrderedItems() as $item)
		{
			$this->setFormItem($item, $form);

			if ($this->order->isMultiAddress && $item->shipment && $item->shipment->shippingAddress)
			{
				$form->set('address_' . $item->getID(), $item->shipment->shippingAddress->getID());
			}
		}

		return $form;
	}

	private function buildOptionsForm(OrderedItem $item, $options)
	{
		$form = new Form($this->buildOptionsValidator($item, $options));
		$this->setFormItem($item, $form);

		return $form;
	}

	private function buildVariationsForm(OrderedItem $item, $variations)
	{
		return new Form($this->buildVariationsValidator($item, $variations));
	}

	private function setFormItem(OrderedItem $item, Form $form)
	{
		$name = 'item_' . $item->getID();
		$form->set($name, $item->count);

		foreach ($item->getOptions() as $option)
		{
			$productOption = $option->choice->option;

			if ($productOption->isBool())
			{
				$value = true;
			}
			else if ($productOption->isText())
			{
				$value = $option->optionText;
			}
			else if ($productOption->isSelect())
			{
				$value = $option->choice->getID();
			}
			else if ($productOption->isFile())
			{
				$value = $option->optionText;
			}

			$form->set($this->getFormFieldName($item, $productOption), $value);
		}
	}

	public function getFormFieldNameAction(OrderedItem $item, $option)
	{
		$optionID = $option instanceof ProductOption ? $option->getID() : $option['ID'];
		return 'itemOption_' . $item->getID() . '_' . $optionID;
	}

	/**
	 * @return \Phalcon\Validation
	 */
	private function buildCartValidator(CustomerOrder $order, $options)
	{
		unset($_SESSION['optionError']);

		$validator = $this->getValidator("cartValidator", $this->request);
		foreach ($order->getOrderedItems() as $item)
		{
			$this->buildItemValidation($validator, $item, $options, $item->getID());
		}

		if ($this->config->get('CHECKOUT_CUSTOM_FIELDS') == 'CART_PAGE')
		{
			$order->getSpecification()->setValidation($validator, true);
		}

		if ($this->isTosInCartPage())
		{
			$validator->add('tos', new Validator\PresenceOf(array('message' => $this->translate('_err_agree_to_tos'))));
		}

		return $validator;
	}

	public function changeRecurringProductPeriodAction()
	{
		$request = $this->getRequest();
		$orderedItemID = $request->get('id');
		$billingPlandropdownName = $request->get('recurringBillingPlan');
		$recurringID = $request->get($billingPlandropdownName);
		$orderedItem = OrderedItem::getInstanceByID($orderedItemID, true);
		$recurringItem = RecurringItem::getInstanceByOrderedItem($orderedItem);
		if ($recurringItem)
		{
			$recurringItem->setRecurringProductPeriod(RecurringProductPeriod::getInstanceByID($recurringID));
			$recurringItem->save();
			$orderedItem->updateBasePriceToCalculatedPrice();
		}
		$this->order->loadItemData();
		$this->order->mergeItems();
		$this->order->save();

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	private function buildOptionsValidator(OrderedItem $item, $options)
	{
		$validator = $this->getValidator("optionValidator", $this->request);
		$this->buildItemValidation($validator, $item, $options);

		return $validator;
	}

	public function buildVariationsValidatorAction(OrderedItem $item, $variations)
	{
		$validator = $this->getValidator('variationValidator', $this->request);
		foreach ($variations['variations'] as $variation)
		{
			$validator->add('variation_' . $variation['ID'], new Validator\PresenceOf(array('message' => $this->translate('_err_option_0'))));
		}

		return $validator;
	}

	private function buildItemValidation(\Phalcon\Validation $validator, $item, $options, $id = null)
	{
		$name = 'item_' . $item->getID();
		$validator->add($name, new IsNumericCheck($this->translate('_err_not_numeric')));
		$validator->addFilter($name, new NumericFilter());

		$productID = $id ? $id : $item->getProduct()->getID();

		if (isset($options['visible'][$productID]))
		{
			foreach ($options['visible'][$productID] as $option)
			{
				if ($option['isRequired'])
				{
					$fieldName = $this->getFormFieldName($item, $option);
					$this->addOptionValidation($validator, $option, $fieldName);
				}
			}
		}

		if (isset($options['more'][$productID]))
		{
			foreach ($options['more'][$productID] as $option)
			{
				if ($option['isRequired'])
				{
					$field = $this->getFormFieldName($item, $option);
					if ($this->request->has($field) || $this->request->has('checkbox_' . $field))
					{
						$this->addOptionValidation($validator, $option, $field);
						/*
						$validator->add($field, new Validator\PresenceOf(array('message' => $this->translate('_err_option_' . $option['type']))));
						*/
						if (!$this->request->get($field))
						{
							$_SESSION['optionError'][$item->getID()][$option['ID']] = true;
						}
					}
				}
			}
		}
	}

	public static function addOptionValidation(\Phalcon\Validation $validator, $option, $fieldName)
	{
/*
		$app = ActiveRecordModel::getApplication();
		if (ProductOption::TYPE_FILE == $option['type'])
		{
			$checks = array(new IsFileUploadedCheck($app->translate('_err_option_upload')),
							new Validator\PresenceOf(array('message' => $this->translate('_err_option_upload')),
							);

			$validator->add($fieldName, new OrCheck(array('upload_' . $fieldName, $fieldName), $checks, $validator->getRequest()));

			if ($types = ProductOption::getFileExtensions($option['fileExtensions']))
			{
				$validator->add('upload_' . $fieldName, new IsFileTypeValidCheck($app->maketext('_err_option_filetype', implode(', ', $types)), $types));
			}

			$validator->add('upload_' . $fieldName, new MaxFileSizeCheck($app->maketext('_err_option_filesize', $option['maxFileSize']), $option['maxFileSize']));
		}
		else
		{
			$validator->add($fieldName, new Validator\PresenceOf(array('message' => $app->translate('_err_option_' . $option['type'])));
		}
*/
	}

	protected function isTosInCartPage()
	{
		return $this->config->get('REQUIRE_TOS') && !$this->config->get('TOS_OPC_ONLY');
	}
}

?>
