<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.Currency');

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
		$this->addBreadCrumb($this->translate('_my_session'), $this->router->createUrlFromRoute($this->request->get('return'), true));
		$this->addBreadCrumb($this->translate('_my_basket'), '');

		$this->order->loadItemData();

		// load product options
		$products = new ARSet();
		foreach ($this->order->getOrderedItems() as $item)
		{
			$products->add($item->product->get());
		}

		$options = ProductOption::loadOptionsForProductSet($products);

		$moreOptions = $optionsArray = array();
		foreach ($this->order->getOrderedItems() as $item)
		{
			if (isset($options[$item->product->get()->getID()]))
			{
				$optionsArray[$item->getID()] = $this->getOptionsArray($options[$item->product->get()->getID()], $item, 'isDisplayedInCart');
				$moreOptions[$item->getID()] = $this->getOptionsArray($options[$item->product->get()->getID()], $item, 'isDisplayed');
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

		$currency = Currency::getValidInstanceByID($this->request->get('currency', $this->application->getDefaultCurrencyCode()), Currency::LOAD_DATA);

		$response = new ActionResponse();
		$response->set('cart', $this->order->toArray());
		$response->set('form', $this->buildCartForm($this->order, $options));
		$response->set('return', $this->request->get('return'));
		$response->set('currency', $currency->getID());
		$response->set('options', $optionsArray);
		$response->set('moreOptions', $moreOptions);
		$response->set('orderTotal', $currency->getFormattedPrice($this->order->getSubTotal($currency)));
		$response->set('expressMethods', $this->application->getExpressPaymentHandlerList(true));
		return $response;
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

			if (!$filter || $option->$filter->get())
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

		$this->order->mergeItems();

		SessionOrder::save($this->order);

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Remove a product from shopping cart
	 */
	public function delete()
	{
		$this->order->removeItem(ActiveRecordModel::getInstanceByID('OrderedItem', $this->request->get('id')));
		SessionOrder::save($this->order);

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	/**
	 *  Add a new product to shopping cart
	 */
	public function addToCart()
	{
		$product = Product::getInstanceByID($this->request->get('id'));
		if (!$product->isAvailable())
		{
			throw new ApplicationException('The product ' . $product->sku->get() . '  is not available for ordering!');
		}

		ClassLoader::import('application.controller.ProductController');
		if (!ProductController::buildAddToCartValidator($product->getOptions(true)->toArray())->isValid())
		{
			return new ActionRedirectResponse('product', 'index', array('id' => $product->getID(), 'query' => 'return=' . $this->request->get('return')));
		}

		ActiveRecordModel::beginTransaction();

		$item = $this->order->addProduct($product, $this->request->get('count', 1));

		if ($item instanceof OrderedItem)
		{
			foreach ($product->getOptions(true) as $option)
			{
				$this->modifyItemOption($item, $option, $this->request, 'option_' . $option->getID());
			}
		}

		$this->order->mergeItems();
		SessionOrder::save($this->order);

		ActiveRecordModel::commit();

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	public function moveToCart()
	{
		$item = $this->order->getItemByID($this->request->get('id'));
		$item->isSavedForLater->set(false);
		$this->order->mergeItems();
		$this->order->resetShipments();
		SessionOrder::save($this->order);

		return new ActionRedirectResponse('order', 'index', array('query' => 'return=' . $this->request->get('return')));
	}

	public function moveToWishList()
	{
		$item = $this->order->getItemByID($this->request->get('id'));
		$item->isSavedForLater->set(true);
		$this->order->mergeItems();
		$this->order->resetShipments();
		SessionOrder::save($this->order);

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
		else if ($request->isValueSet($varName))
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
	 *	@todo Optimize loading of product options
	 */
	private function buildCartForm(CustomerOrder $order, $options)
	{
		ClassLoader::import("framework.request.validator.Form");

		$form = new Form($this->buildCartValidator($order, $options));

		foreach ($order->getOrderedItems() as $item)
		{
			$this->setFormItem($item, $form);
		}

		return $form;
	}

	private function buildOptionsForm(OrderedItem $item, $options)
	{
		ClassLoader::import("framework.request.validator.Form");

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

	public function getFormFieldName(OrderedItem $item, ProductOption $option)
	{
		return 'itemOption_' . $item->getID() . '_' . $option->getID();
	}

	/**
	 * @return RequestValidator
	 */
	private function buildCartValidator(CustomerOrder $order, $options)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("cartValidator", $this->request);

		foreach ($order->getOrderedItems() as $item)
		{
			$this->buildItemValidation($validator, $item, $options);
		}

		return $validator;
	}

	private function buildOptionsValidator(OrderedItem $item, $options)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

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
		if (isset($options[$productID]))
		{
			foreach ($options[$productID] as $option)
			{
				if ($option->isRequired->get())
				{
					$validator->addCheck($this->getFormFieldName($item, $option), new IsNotEmptyCheck($this->translate('_err_option_' . $option->type->get())));
				}
			}
		}
	}
}

?>