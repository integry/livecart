<?php

ClassLoader::import('library.payment.TransactionDetails');

/**
 * Create a new transaction to be passed to payment gateway for authorization based on LiveCart order data
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class LiveCartTransaction extends TransactionDetails
{
	protected $order;

	public function __construct(CustomerOrder $order, Currency $currency)
	{
		parent::__construct();

		$this->order = $order;

		$order->loadAll();

		// billing address
		if ($address = $order->billingAddress->get())
		{
			$fields = array('firstName', 'lastName', 'companyName', 'phone', 'city', 'postalCode', 'countryID' => 'country');
			foreach ($fields as $key => $field)
			{
				$addressField = is_numeric($key) ? $field : $key;
				$this->$field->set($address->$addressField->get());
			}

			$this->state->set($this->getStateValue($address));
			$this->address->set($address->address1->get() . ' ' . $address->address2->get());
		}

		// shipping address
		$address = $order->shippingAddress->get();
		if (!$address)
		{
			$address = $order->billingAddress->get();
		}

		if ($address)
		{
			foreach ($fields as $key => $field)
			{
				$addressField = is_numeric($key) ? $field : $key;
				$field = 'shipping' . ucfirst($field);
				$this->$field->set($address->$addressField->get());
			}

			$this->shippingState->set($this->getStateValue($address));
			$this->shippingAddress->set($address->address1->get() . ' ' . $address->address2->get());
		}

		// amount
		$order->currency->set($currency);
		$this->amount->set(round($order->getDueAmount(), 2));
		$this->currency->set($currency->getID());

		// transaction identification
		$this->invoiceID->set($order->getID());

		if (isset($_SERVER['REMOTE_ADDR']))
		{
			$this->ipAddress->set($_SERVER['REMOTE_ADDR']);
		}

		// customer identification
		if ($order->user->get())
		{
			$order->user->get()->load();
			$this->shippingEmail->set($order->user->get()->email->get());
			$this->email->set($order->user->get()->email->get());
			$this->clientID->set($order->user->get()->getID());
		}

		// order details

		// load variation data
		$variations = new ProductSet();
		foreach ($order->getShoppingCartItems() as $item)
		{
			if ($item->product->get() && $item->product->get()->parent->get())
			{
				$variations->unshift($item->product->get());
			}
		}

		if ($variations->size())
		{
			$variations->loadVariations();
		}

		foreach ($order->getShoppingCartItems() as $item)
		{
			$product = $item->getProduct();
			$variations = array();
			foreach ($product->getRegisteredVariations() as $variation)
			{
				$variations[] = $variation->getValueByLang('name');
			}

			$this->addLineItem($product->getName() . ($variations ? ' (' . implode(' / ', $variations) . ')' : ''), $item->getPrice(false), $item->count->get(), $product->sku->get());
		}

		if ($discount = $order->getFixedDiscountAmount())
		{
			$this->addLineItem(CustomerOrder::getApplication()->translate('_discount'), $discount * -1, 1, 'discount');
		}

		foreach ($order->getShipments() as $shipment)
		{
			if ($rate = $shipment->getSelectedRate())
			{
				$rate = $rate->toArray();
				$name = empty($rate['ShippingService']['name_lang']) ? $rate['serviceName'] : $rate['ShippingService']['name_lang'];
				$this->addLineItem($name, $shipment->getShippingTotalBeforeTax(), 1, 'shipping');
			}
		}

		if ($taxes = $order->getTaxBreakdown())
		{
			foreach ($taxes as $id => $amount)
			{
				$tax = Tax::getInstanceById($id, true);
				$this->addLineItem($tax->getValueByLang('name', null), $amount, 1, 'tax');
			}
		}
	}

	public function getOrder()
	{
		return $this->order;
	}

	private function getStateValue(UserAddress $address)
	{
		if ($state = $address->state->get())
		{
			if (!$state->isLoaded())
			{
				$state->load();
			}

			if ($state->code->get() && !is_numeric($state->code->get()))
			{
				return $state->code->get();
			}
			else
			{
				return $state->name->get();
			}
		}
		else
		{
			return $address->stateName->get();
		}
	}
}

?>
