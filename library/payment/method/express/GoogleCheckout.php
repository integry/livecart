<?php

include_once(dirname(__file__) . '/../../abstract/ExpressPayment.php');

/**
 *
 * @package library.payment.method.express
 * @author Integry Systems
 */
class GoogleCheckout extends ExpressPayment
{
	protected $data;

	public function getInitUrl($returnUrl, $cancelUrl, $sale = true)
	{
		$router = ActiveRecordModel::getApplication()->getRouter();
		$completeUrl = $router->createFullUrl($router->createUrl(array('controller' => 'checkout', 'action' => 'completeExternal', 'id' => $this->order->getID())));
		$handler = $this->getHandler($cancelUrl, $completeUrl);

		$sandbox = $this->getConfigValue('sandbox') ? 'sandbox.' : '';
		$url = $sandbox ? 'https://sandbox.google.com/checkout/api/checkout/v2/merchantCheckout/Merchant/':
						  'https://checkout.google.com/api/checkout/v2/merchantCheckout/Merchant/';
		$parsed = new XML_Unserializer();
		$cart = $handler->getCart();
		//echo $cart;
		if (($response = $handler->_getCurlResponse($cart, $url . $this->getConfigValue('merchant_id'))) && ($parsed->unserialize($response)))
		{
			$array = $parsed->getUnserializedData();

			if (empty($array['redirect-url']))
			{
				var_dump($array);
				return false;
			}

			$url = $array['redirect-url'];
			$url = str_replace('shoppingcartshoppingcart', 'shoppingcart&shoppingcart', $url);
			return $url;
		}
		else
		{
			return false;
		}
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function isCreditable()
	{
		return false;
	}

	public function isVoidable()
	{
		return false;
	}

	public function isMultiCapture()
	{
		return false;
	}

	public function isCapturedVoidable()
	{
		return false;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : 'USD';
	}

	public static function getSupportedCurrencies()
	{
		return array('GBP', 'USD');
	}

	public function extractTransactionResult($array)
	{
		$result = new TransactionResult();

		$result->gatewayTransactionID->set($array['GOOGLE-ORDER-NUMBER'][0]['VALUE']);
		$total = $array['ORDER-TOTAL'][0];
		$result->amount->set($total['VALUE']);
		$result->currency->set($total['ATTRIBUTES']['CURRENCY']);

		$result->rawResponse->set($array);

		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		return $this->processAuth('DoExpressCheckoutPayment', 'Order');
	}

	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->processCapture();
	}

	/**
	 *	Void the payment (issue full credit)
	 */
	public function void()
	{
		return $this->processVoid();
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->processAuth('DoExpressCheckoutPayment', 'Sale');
	}

	public function getHandler($returnUrl = '', $cancelUrl = '')
	{
		$application = ActiveRecordModel::getApplication();
		$GLOBALS['merchant_id'] = $this->getConfigValue('merchant_id');
		if ($this->getConfigValue('sandbox') && !defined('PHPGCHECKOUT_USE_SANDBOX'))
		{
			define('PHPGCHECKOUT_USE_SANDBOX', true);
		}

		include_once dirname(dirname(__file__)) . '/library/google/config.php';

		$handler = new gCart($this->getConfigValue('merchant_id'), $this->getConfigValue('merchant_key'));
		$handler->setMerchantCheckoutFlowSupport($returnUrl, $cancelUrl, $this->application->getConfig()->get('REQUIRE_PHONE'));

		// add cart items
		if ($this->order)
		{
			$items = array();
			foreach ($this->order->getOrderedItems() as $item)
			{
				if (!$item->isSavedForLater->get())
				{
					$gItem = new gItem(htmlspecialchars($item->product->get()->getValueByLang('name')), htmlspecialchars($item->product->get()->getValueByLang('shortDescription')), $item->count->get(), $item->price->get());
					$gItem->setPrivateItemData('<item-id>' . $item->getID() . '</item-id><order-id>' . $this->order->getID() . '</order-id>');
					$items[] = $gItem;
				}

				// add discounts
				if ($discounts = $this->order->getFixedDiscountAmount())
				{
					$items[] = new gItem($application->translate('_discount'), '', 1, $discounts * -1);
				}

				$handler->addItems($items);
			}

			// get shipping rates for all zones - silly, eh?
			if ($this->order->isShippingRequired())
			{
				$shipment = $this->order->getShipments()->get(0);
				$zoneCountries = $zoneStates = $zoneZips = array();
				foreach (DeliveryZone::getAll() as $zone)
				{
					$countries = $zone->getCountries()->extractField('countryCode');

					$states = array();
					foreach ($zone->getStates()->extractReferencedItemSet('state') as $state)
					{
						if ($state->countryID == 'US')
						{
							$states[] = $state->code->get();
						}
						else
						{
							$countries[] = $state->countryID->get();
						}
					}

					$countries = array_unique($countries);

					$zipMasks = $zone->getZipMasks()->extractField('mask');

					foreach ($zone->getShippingRates($shipment)->toArray() as $rate)
					{
						$name = $rate['serviceName'] ? $rate['serviceName'] : $rate['ShippingService']['name_lang'];
						$gRate = new gShipping($name, round($rate['costAmount'], 2), 'merchant-calculated-shipping');
						$gRate->addAllowedAreas($countries, $states, $zipMasks);
						$shipping[$name] = $gRate;
					}

					$zoneCountries = array_merge($zoneCountries, $countries);
					$zoneStates = array_merge($zoneStates, $states);
					$zoneZips = array_merge($zoneZips, $zipMasks);
				}

				// default zone
				$enabledCountries = array_keys($application->getConfig()->get('ENABLED_COUNTRIES'));
				$defCountries = array_intersect($enabledCountries, $zoneCountries);

				foreach (DeliveryZone::getDefaultZoneInstance()->getShippingRates($shipment)->toArray() as $rate)
				{
					$gRate = new gShipping($rate['serviceName'] ? $rate['serviceName'] : $rate['ShippingService']['name_lang'], round($rate['costAmount'], 2), 'merchant-calculated-shipping');
					$gRate->addAllowedAreas($defCountries, array(), array());
					$shipping[] = $gRate;
				}

				$handler->_setShipping($shipping);
			}
		}
var_dump($shipping);
		// set merchant calculations
		$router = CustomerOrder::getApplication()->getRouter();
		$calcUrl = $router->createFullUrl($router->createUrl(array('controller' => 'googleCheckout', 'action' => 'index')), !$this->getConfigValue('sandbox'));
		$handler->setMerchantCalculations(new gMerchantCalculations($calcUrl, $this->getConfigValue('coupons')));
		$handler->setDefaultTaxTable(new gTaxTable('Tax', array(new gTaxRule(0))));

		return $handler;
	}
}

?>