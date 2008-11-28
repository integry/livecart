<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.order.OrderedItem');
ClassLoader::import('application.model.order.SessionOrder');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductOption');

/**
 * @author Integry Systems
 * @package application.controller
 */
class OrderController extends FrontendController
{
	/**
	 * @var CustomerOrder
	 */
	protected $order;

	/**
	 *  View shopping cart contents
	 */
	public function index()
	{
		if ($this->order->isMultiAddress->get())
		{
			return new ActionRedirectResponse('order', 'multi');
		}

		$response = $this->getCartPageResponse();
		$this->addBreadCrumb($this->translate('_my_basket'), '');
		return $response;
	}

	/**
	 *	@role login
	 */
	public function multi()
	{
		if (!$this->order->isMultiAddress->get())
		{
			return new ActionRedirectResponse('order', 'index');
		}

		$response = $this->getCartPageResponse();

		// we're loading through a set, because all referenced records need to be loaded before array transformation
		$addresses = array();
		foreach ($this->user->getShippingAddressSet()->toArray() as $address)
		{
			$addresses[$address['UserAddress']['ID']] = $address['UserAddress']['compact'];
		}
		$response->set('addresses', $addresses);

		$this->addBreadCrumb($this->translate('_select_shipping_addresses'), '');
		return $response;
	}

	private function getCartPageResponse()
	{
		$this->addBreadCrumb($this->translate('_my_session'), $this->router->createUrlFromRoute($this->request->get('return'), true));

		$this->order->loadItemData();

		$response = new ActionResponse();
		if ($result = $this->order->updateToStock())
		{
			$response->set('changes', $result);
		}

		$options = $this->getItemOptions();

		$currency = Currency::getValidInstanceByID($this->request->get('currency', $this->application->getDefaultCurrencyCode()), Currency::LOAD_DATA);

		$form = $this->buildCartForm($this->order, $options);

		$orderArray = $this->order->toArray();
		$itemsById = array();
		foreach (array('cartItems', 'wishListItems') as $type)
		{
			if (!empty($orderArray[$type]))
			{
				foreach ($orderArray[$type] as &$item)
				{
					$itemsById[$item['ID']] =& $item;
				}
			}
		}

		$response->set('cart', $orderArray);
		$response->set('itemsById', $itemsById);
		$response->set('form', $form);
		$response->set('return', $this->request->get('return'));
		$response->set('currency', $currency->getID());
		$response->set('options', $options['visible']);
		$response->set('moreOptions', $options['more']);
		$response->set('orderTotal', $currency->getFormattedPrice($this->order->getSubTotal($currency)));
		$response->set('expressMethods', $this->application->getExpressPaymentHandlerList(true));
		$response->set('isCouponCodes', DiscountCondition::isCouponCodes());

		$this->order->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	private function getItemOptions()
	{
		// load product options
		$products = array();
		foreach ($this->order->getOrderedItems() as $item)
		{
			$products[$item->product->get()->getID()] = $item->product->get();
		}

		$options = ProductOption::loadOptionsForProductSet(ARSet::buildFromArray($products));

		$moreOptions = $optionsArray = array();
		foreach ($this->order->getOrderedItems() as $item)
		{
			$productID = $item->product->get()->getID();
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

	public function options()
	{
		$response = $this->index();
		$response->set('editOption', $this->request->get('id'));
		return $response;
	}

	public function optionForm(CustomerOrder $order = null, $filter = 'isDisplayed')
	{
		$order = $order ? $order : $this->order;

		$item = $order->getItemByID($this->request->get('id'));
		$options = $optionsArray = array();
		$product = $item->product->get();
		$options[$product->getID()] = $product->getOptions(true);
		$optionsArray[$item->getID()] = $this->getOptionsArray($options[$product->getID()], $item, $filter);

		$this->setLayout('empty');

		$response = new ActionResponse();
		$response->set('form', $this->buildOptionsForm($item, $options));
		$response->set('options', $optionsArray);
		$response->set('item', $item->toArray());
		return $response;
	}

	private function getOptionsArray($set, $item, $filter = 'isDisplayed')
	{
		$out = array();
		foreach ($set as $option)
		{
			$arr = $option->toArray();
			$arr['fieldName'] = $this->getFormFieldName($item, $option);

			$invalid = !empty($_SESSION['optionError'][$item->getID()][$option->getID()]) && ('isDisplayedInCart' == $filter);

			if (!$filter || $option->$filter->get() || $invalid)
			{
				$out[] = $arr;
			}
		}

		return $out;
	}

	/**
	 *  Update product quantities
	 */
	public function update()
	{
		// coupon code
		if ($this->request->get('coupon'))
		{
			$code = $this->request->get('coupon');

			if ($condition = DiscountCondition::getInstanceByCoupon($code))
			{
				$exists = false;
				foreach ($this->order->getCoupons() as $coupon)
				{
					if ($coupon->couponCode->get() == $code)
					{
						$exists = true;
					}
				}

				if (!$exists)
				{
					OrderCoupon::getNewInstance($this->order, $code)->save();
				}

				$this->setMessage($this->makeText('_coupon_added', array($code)));
			}
			else
			{
				$this->setErrorMessage($this->makeText('_coupon_not_found', array($code)));
			}

			$this->order->getCoupons(true);
		}

		$this->order->loadItemData();
		$validator = $this->buildCartValidator($this->order, $this->getItemOptions());

		if (!$validator->isValid())
		{
			return new ActionRedirectResponse('order', 'index');
		}

		$this->order->loadRequestData($this->request);

		foreach ($this->order->getOrderedItems() as $item)
		{
			if ($this->request->isValueSet('item_' . $item->getID()))
			{
				foreach ($item->product->get()->getOptions(true) as $option)
				{
					$this->modifyItemOption($item, $option, $this->request, $this->getFormFieldName($item, $option));
				}

				$item->save();

				$this->order->updateCount($item, $this->request->get('item_' . $item->getID(), 0));
			}
		}

		if ($this->order->isMultiAddress->get())
		{
			$addresses = $this->user->getShippingAddressSet();
			$this->order->getShipments();

			foreach ($this->order->getOrderedItems() as $item)
			{
				if ($addressId = $this->request->get('address_' . $item->getID()))
				{
					if (!$item->shipment->get() || !$item->shipment->get()->shippingAddress->get() || ($item->shipment->get()->shippingAddress->get()->getID() != $addressId))
					{
						foreach ($this->order->getShipments() as $shipment)
						{
							if ($shipment->shippingAddress->get() && ($shipment->shippingAddress->get()->getID() == $addressId))
							{
								if (!$item->shipment->get() || ($item->shipment->get()->getID() != $shipment->getID()))
								{
									if ($item->shipment->get())
									{
										$item->shipment->get()->removeItem($item);
									}

									$shipment->addItem($item);
									break;
								}
							}

							$shipment = null;
						}

						if (!isset($shipment) || !$shipment)
						{
							$address = ActiveRecordModel::getInstanceById('UserAddress', $addressId, true);

							$shipment = Shipment::getNewInstance($this->order);
							$shipment->shippingAddress->set($address);
							$shipment->save();
							$this->order->addShipment($shipment);

							$shipment->addItem($item);
						}

						$item->save();
					}
				}

				if ($item->shipment->get())
				{
					$item->shipment->get()->shippingServiceData->set(null);
					$item->shipment->get()->save();
				}
			}
		}

		$this->order->mergeItems();

		SessionOrder::save($this->order);

		// proceed with the checkout
		if ($this->request->get('proceed'))
		{
			return new ActionRedirectResponse('checkout', 'index');
		}

		// redirect to payment gateway
		if ($url = $this->request->get('redirect'))
		{
			return new RedirectResponse($url);
		}

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Remove a product from shopping cart
	 */
	public function delete()
	{
		$item = ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->get('id'), ActiveRecordModel::LOAD_DATA, array('Product'));
		$this->setMessage($this->makeText('_removed_from_cart', array($item->product->get()->getName($this->getRequestLanguage()))));
		$this->order->removeItem($item);
		SessionOrder::save($this->order);

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Add a new product to shopping cart
	 */
	public function addToCart()
	{
		$product = Product::getInstanceByID($this->request->get('id'), true, array('Category'));

		$productRedirect = new ActionRedirectResponse('product', 'index', array('id' => $product->getID(), 'query' => 'return=' . $this->request->get('return')));
		if (!$product->isAvailable())
		{
			$productController = new ProductController($this->application);
			$productController->setErrorMessage($this->translate('_product_unavailable'));
			return $productRedirect;
		}

		$variations = $product->getVariationData($this->application);
		ClassLoader::import('application.controller.ProductController');
		if (!ProductController::buildAddToCartValidator($product->getOptions(true)->toArray(), $variations)->isValid())
		{
			return $productRedirect;
		}

		// check if a variation needs to be added to cart instead of a parent product
		if ($variations)
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

			$product = Product::getInstanceByID($variations['products'][$hash]['ID'], Product::LOAD_DATA);
		}

		ActiveRecordModel::beginTransaction();

		$count = $this->request->get('count', 1);
		if ($count < $product->getMinimumQuantity())
		{
			$count = $product->getMinimumQuantity();
		}
		$item = $this->order->addProduct($product, $count);

		if ($item instanceof OrderedItem)
		{
			foreach ($product->getOptions(true) as $option)
			{
				$this->modifyItemOption($item, $option, $this->request, 'option_' . $option->getID());
			}

			if ($this->order->isMultiAddress->get())
			{
				$item->save();
			}
		}

		$this->order->mergeItems();
		SessionOrder::save($this->order);

		ActiveRecordModel::commit();

		$this->setMessage($this->makeText('_added_to_cart', array($product->getName($this->getRequestLanguage()))));

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	public function moveToCart()
	{
		$item = $this->order->getItemByID($this->request->get('id'));
		$item->isSavedForLater->set(false);
		$this->order->mergeItems();
		$this->order->resetShipments();
		SessionOrder::save($this->order);

		$this->setMessage($this->makeText('_moved_to_cart', array($item->product->get()->getName('name', $this->getRequestLanguage()))));

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	public function moveToWishList()
	{
		$item = $this->order->getItemByID($this->request->get('id'));
		$item->isSavedForLater->set(true);
		$this->order->mergeItems();
		$this->order->resetShipments();
		SessionOrder::save($this->order);

		$this->setMessage($this->makeText('_moved_to_wishlist', array($item->product->get()->getName('name', $this->getRequestLanguage()))));

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Add a new product to wish list (save items for buying later)
	 */
	public function addToWishList()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA);

		$this->order->addToWishList($product);
		$this->order->mergeItems();
		SessionOrder::save($this->order);

		$this->setMessage($this->makeText('_added_to_wishlist', array($product->getName($this->getRequestLanguage()))));

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	public function modifyItemOption(OrderedItem $item, ProductOption $option, Request $request, $varName)
	{
		if ($option->isBool() && $request->isValueSet('checkbox_' . $varName))
		{
			if ($request->get($varName))
			{
				$item->addOptionChoice($option->defaultChoice->get());
			}
			else
			{
				$item->removeOption($option);
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
					$choice = $item->addOptionChoice($option->defaultChoice->get());
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
	public function setMultiAddress()
	{
		if (!$this->config->get('ENABLE_MULTIADDRESS'))
		{
			return new ActionRedirectResponse('order', 'index');
		}

		$this->order->isMultiAddress->set(true);
		$this->order->shippingAddress->set(null);

		// split items
		foreach ($this->order->getOrderedItems() as $item)
		{
			if ($item->count->get() > 1)
			{
				$count = $item->count->get();
				$item->count->set(1);
				for ($k = 1; $k < $count; $k++)
				{
					$this->order->addItem(clone $item);
				}
			}
		}

		$this->order->save();

		return new ActionRedirectResponse('order', 'multi');
	}

	public function setSingleAddress()
	{
		$f = new ARUpdateFilter(new EqualsCond(new ARFieldHandle('OrderedItem', 'customerOrderID'), $this->order->getID()));
		$f->addModifier('OrderedItem.shipmentID', new ARExpressionHandle('NULL'));
		ActiveRecordModel::updateRecordSet('OrderedItem', $f);

		$this->order->isMultiAddress->set(false);
		$this->order->loadAll();
		$this->order->mergeItems();
		$this->order->resetShipments();

		SessionOrder::save($this->order);
		$this->order->deleteRelatedRecordSet('Shipment');
		return new ActionRedirectResponse('order', 'index');
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

			if ($this->order->isMultiAddress->get() && $item->shipment->get() && $item->shipment->get()->shippingAddress->get())
			{
				$form->set('address_' . $item->getID(), $item->shipment->get()->shippingAddress->get()->getID());
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

	private function setFormItem(OrderedItem $item, Form $form)
	{
		$name = 'item_' . $item->getID();
		$form->set($name, $item->count->get());

		foreach ($item->getOptions() as $option)
		{
			$productOption = $option->choice->get()->option->get();

			if ($productOption->isBool())
			{
				$value = true;
			}
			else if ($productOption->isText())
			{
				$value = $option->optionText->get();
			}
			else if ($productOption->isSelect())
			{
				$value = $option->choice->get()->getID();
			}

			$form->set($this->getFormFieldName($item, $productOption), $value);
		}
	}

	public function getFormFieldName(OrderedItem $item, $option)
	{
		$optionID = $option instanceof ProductOption ? $option->getID() : $option['ID'];
		return 'itemOption_' . $item->getID() . '_' . $optionID;
	}

	/**
	 * @return RequestValidator
	 */
	private function buildCartValidator(CustomerOrder $order, $options)
	{
		unset($_SESSION['optionError']);

		$validator = new RequestValidator("cartValidator", $this->request);

		foreach ($order->getOrderedItems() as $item)
		{
			$this->buildItemValidation($validator, $item, $options);
		}

		if ($this->config->get('CHECKOUT_CUSTOM_FIELDS') == 'CART_PAGE')
		{
			$order->getSpecification()->setValidation($validator, true);
		}

		return $validator;
	}

	private function buildOptionsValidator(OrderedItem $item, $options)
	{
		$validator = new RequestValidator("optionValidator", $this->request);
		$this->buildItemValidation($validator, $item, $options);

		return $validator;
	}

	private function buildItemValidation(RequestValidator $validator, $item, $options)
	{
		$name = 'item_' . $item->getID();
		$validator->addCheck($name, new IsNumericCheck($this->translate('_err_not_numeric')));
		$validator->addFilter($name, new NumericFilter());

		$productID = $item->product->get()->getID();

		if (isset($options['visible'][$productID]))
		{
			foreach ($options['visible'][$productID] as $option)
			{
				if ($option['isRequired'])
				{
					$validator->addCheck($this->getFormFieldName($item, $option), new IsNotEmptyCheck($this->translate('_err_option_' . $option['type'])));
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
					if ($this->request->isValueSet($field) || $this->request->isValueSet('checkbox_' . $field))
					{
						$validator->addCheck($field, new IsNotEmptyCheck($this->translate('_err_option_' . $option['type'])));
						if (!$this->request->get($field))
						{
							$_SESSION['optionError'][$item->getID()][$option['ID']] = true;
						}
					}
				}
			}
		}
	}
}

?>