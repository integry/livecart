<?php


/**
 * Create a new transaction to be passed to payment gateway for authorization based on LiveCart order data
 *
 * @package application/model/order
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
		if ($address = $order->billingAddress)
		{
			$fields = array('firstName', 'lastName', 'companyName', 'phone', 'city', 'postalCode', 'countryID' => 'country');
			foreach ($fields as $key => $field)
			{
				$addressField = is_numeric($key) ? $field : $key;
				$this->$field->set($address->$addressField);
			}

			$this->state->set($this->getStateValue($address));
			$this->address->set($address->address1 . ' ' . $address->address2);
		}

		// shipping address
		$address = $order->shippingAddress;
		if (!$address)
		{
			$address = $order->billingAddress;
		}

		if ($address)
		{
			foreach ($fields as $key => $field)
			{
				$addressField = is_numeric($key) ? $field : $key;
				$field = 'shipping' . ucfirst($field);
				$this->$field->set($address->$addressField);
			}

			$this->shippingState->set($this->getStateValue($address));
			$this->shippingAddress->set($address->address1 . ' ' . $address->address2);
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
		if ($order->user)
		{
			$order->user->load();
			$this->shippingEmail->set($order->user->email);
			$this->email->set($order->user->email);
			$this->clientID->set($order->user->getID());
		}

		// order details

		// load variation data
		$variations = new ProductSet();
		foreach ($order->getShoppingCartItems() as $item)
		{
			if ($item->product && $item->product->parent)
			{
				$variations->unshift($item->product);
			}
		}

		if ($variations->count())
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

			$ri = RecurringItem::getInstanceByOrderedItem($item);
			if ($ri && $ri->isExistingRecord())
			{
				$ri->load();
			}
			else
			{
				$ri = null;
			}

			$name = $product->getName() ? $product->getName() : $item->getValueByLang('name');

			$this->addLineItem(
				$name . ($variations ? ' (' . implode(' / ', $variations) . ')' : ''),
				$item->getPrice(false),
				$item->count,
				$product->sku,
				$ri
			);
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

	public function getorderBy()
	{
		return $this->order;
	}

	private function getStateValue(UserAddress $address)
	{
		if ($state = $address->state)
		{
			if (!$state->isLoaded())
			{
				$state->load();
			}

			if ($state->code && !is_numeric($state->code))
			{
				return $state->code;
			}
			else
			{
				return $state->name;
			}
		}
		else
		{
			return $address->stateName;
		}
	}
}

?>
