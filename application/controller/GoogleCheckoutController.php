<?php

ClassLoader::import('application.controller.CheckoutController');
ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('library.payment.method.express.GoogleCheckout');
ClassLoader::import('library.shipping.library.usps.xmlparser');

/**
 *  Handles Google Checkout callbacks
 *
 * @author Integry Systems
 * @package application.controller
 */
class GoogleCheckoutController extends CheckoutController
{
	public function index()
	{
		$input = '<new-order-notification xmlns="http://checkout.google.com/schema/2" serial-number="674859982453950-00001-7">
  <timestamp>2008-11-27T20:58:43.928Z</timestamp>
  <shopping-cart>
    <items>
      <item>
        <item-name>Nokia 1600</item-name>
        <item-description>The all new Nokia 1600 phone makes life so much easier! Put yourself centre-stage with the 65,536 colour screen and spice it up with the MP3 and 20-chord polyphonic ringing tones. With the speaking alarm and clock, youll never miss a party again. Break through your daily routine and enjoy more o...</item-description>
        <quantity>1</quantity>
        <unit-price currency="USD">79.04</unit-price>
        <merchant-private-item-data>
          <item-id>327</item-id>
          <order-id>85</order-id>
        </merchant-private-item-data>
      </item>
      <item>
        <item-name>Nokia 7373</item-name>
        <item-description>The Nokia 7373 is a fashion style leader with its swivel design and high-resolution 320x240, 262K color display. The Nokia 7373 GSM triple-band device features include a 2.0-megapixel camera with flash and 8x Zoom, video recorder, music player, Bluetooth 2.0 and Java MIDP 2.0.</item-description>
        <quantity>1</quantity>
        <unit-price currency="USD">364.88</unit-price>
        <merchant-private-item-data>
          <item-id>328</item-id>
          <order-id>85</order-id>
        </merchant-private-item-data>
      </item>
    </items>
  </shopping-cart>
  <order-adjustment>
    <merchant-calculation-successful>true</merchant-calculation-successful>
    <merchant-codes />
    <total-tax currency="USD">56.91</total-tax>
    <shipping>
      <merchant-calculated-shipping-adjustment>
        <shipping-name>American Rate By Weight</shipping-name>
        <shipping-cost currency="USD">30.36</shipping-cost>
      </merchant-calculated-shipping-adjustment>
    </shipping>
    <adjustment-total currency="USD">87.27</adjustment-total>
  </order-adjustment>
  <buyer-id>131641297972917</buyer-id>
  <google-order-number>674859982453950</google-order-number>
  <buyer-shipping-address>
    <email>rinalds@integry.net</email>
    <company-name></company-name>
    <contact-name>Rinalds US</contact-name>
    <phone></phone>
    <fax></fax>
    <address1>test</address1>
    <address2></address2>
    <country-code>US</country-code>
    <city>test</city>
    <region>CA</region>
    <postal-code>90210</postal-code>
  </buyer-shipping-address>
  <buyer-billing-address>
    <email>rinalds@integry.net</email>
    <company-name></company-name>
    <contact-name>Rinalds Uzkalns</contact-name>
    <phone>+371-25977725</phone>
    <fax></fax>
    <address1>Stacijas 4a</address1>
    <address2></address2>
    <country-code>LV</country-code>
    <city>Saulkrasti</city>
    <region></region>
    <postal-code>2610</postal-code>
  </buyer-billing-address>
  <buyer-marketing-preferences>
    <email-allowed>true</email-allowed>
  </buyer-marketing-preferences>
  <order-total currency="USD">531.19</order-total>
  <fulfillment-order-state>NEW</fulfillment-order-state>
  <financial-order-state>REVIEWING</financial-order-state>
</new-order-notification>';

		$handler = $this->application->getPaymentHandler('GoogleCheckout')->getHandler();
		$array = $origArray = $this->getArrayFromXML($input);
		$array = $this->getPostData();

		$array = array_shift(array_shift($array));

		if (isset($array['CALCULATE']))
		{
			return $this->calculationCallback($array);
		}
		else if (isset($origArray['NEW-ORDER-NOTIFICATION']))
		{
			return $this->completeCallback($array);
		}
	}

	private function completeCallback($array)
	{
		ActiveRecordModel::beginTransaction();
		$order = $this->createOrder($array);
		$email = null;
		$address = null;

		if (isset($array['BUYER-BILLING-ADDRESS']))
		{
			$order->billingAddress->set($this->getUserAddress($array['BUYER-BILLING-ADDRESS'][0]));
			if (isset($array['BUYER-BILLING-ADDRESS'][0]['EMAIL']))
			{
				$email = $array['BUYER-BILLING-ADDRESS'][0]['EMAIL'][0]['VALUE'];
			}
		}

		if (isset($array['BUYER-SHIPPING-ADDRESS']))
		{
			$order->shippingAddress->set($this->getUserAddress($array['BUYER-SHIPPING-ADDRESS'][0]));
			if (isset($array['BUYER-SHIPPING-ADDRESS'][0]['EMAIL']))
			{
				$email = $array['BUYER-SHIPPING-ADDRESS'][0]['EMAIL'][0]['VALUE'];
			}
		}

		if (isset($array['ORDER-ADJUSTMENT'][0]['SHIPPING'][0]['MERCHANT-CALCULATED-SHIPPING-ADJUSTMENT'][0]))
		{
			$shipping = $array['ORDER-ADJUSTMENT'][0]['SHIPPING'][0]['MERCHANT-CALCULATED-SHIPPING-ADJUSTMENT'][0];
			$shippingName = $shipping['SHIPPING-NAME'][0]['VALUE'];

			$shipment = $order->getShipments()->get(0);
			foreach ($shipment->getAvailableRates() as $rate)
			{
				$rate = $rate->toArray();
				if (($rate['serviceName'] == $shippingName) || (!empty($rate['ShippingService']) && ($rate['ShippingService']['name_lang'] == $shippingName)))
				{
					$shipment->setRateId($rate['serviceID']);
				}
			}
		}

		if (!$email)
		{
			$email = $array['BUYER-ID'][0]['VALUE'] . '@googlecheckout.com';
		}

		$user = User::getInstanceByEmail($email);
		if (!$user)
		{
			$address = $order->billingAddress->get();
			$user = User::getNewInstance($email);
			foreach (array('firstName', 'lastName', 'companyName') as $field)
			{
				$user->$field->set($address->$field->get());
			}

			$user->save();
		}

		$order->user->set($user);
		$order->save();
		$this->order = $order;

		$handler = $this->application->getPaymentHandler('GoogleCheckout');
		$this->registerPayment($handler->extractTransactionResult($array), $handler);
		ActiveRecordModel::commit();
	}

	private function getUserAddress($address)
	{
		$ua = UserAddress::getNewInstance();
		foreach (array('COMPANY-NAME' => 'companyName', 'CONTACT-NAME' => 'firstName', 'PHONE' => 'phone', 'ADDRESS1' => 'address1', 'ADDRESS2' => 'address2', 'COUNTRY-CODE' => 'countryID', 'POSTAL-CODE' => 'postalCode', 'CITY' => 'city', 'REGION' => 'stateName') as $google => $livecart)
		{
			$ua->$livecart->set($address[$google][0]['VALUE']);
		}

		// state lookup
		if ($stateID = State::getStateIDByName($ua->countryID->get(), $ua->stateName->get()))
		{
			$ua->state->set(State::getInstanceByID($stateID, true));
		}

		$ua->countryID->set($address['COUNTRY-CODE'][0]['VALUE']);

		$names = explode(' ', $ua->firstName->get(), 2);
		$ua->firstName->set(array_shift($names));
		$ua->lastName->set(array_shift($names));

		$ua->save();

		return $ua;
	}

	private function calculationCallback($array)
	{
		$order = $this->createOrder($array);
		$allRates = $this->getShippingRates($order, $this->getAddresses($array));

		$results = new SimpleXMLElement('<results></results>');
		$foundRates = array();
		$calcTaxes = ('true' == $array['CALCULATE'][0]['TAX'][0]['VALUE']);

		foreach ($allRates as $addressID => $rates)
		{
			foreach ($rates as $rate)
			{
				$currency = $rate['costCurrency'];
				$el = new SimpleXMLElement('<result></result>');
				$name = $rate['serviceName'] ? $rate['serviceName'] : $rate['ShippingService']['name_lang'];
				$foundRates[$name] = true;
				$el->addAttribute('shipping-name', $name);
				$el->addAttribute('address-id', $addressID);

				$sRate = $el->addChild('shipping-rate');
				$sRate->addAttribute('currency', $currency);
				$this->xmlValue($sRate, round($rate['costAmount'], 2));

				$sRate = $el->addChild('shippable');
				$this->xmlValue($sRate, 'true');

				if ($calcTaxes)
				{
					$sRate = $el->addChild('total-tax');
					$sRate->addAttribute('currency', $currency);
					$this->xmlValue($sRate, round($rate['orderTax'], 2));
				}

				$this->xmlAppend($results, $el);
			}
		}

		foreach($array['CALCULATE'][0]['SHIPPING'][0]['METHOD'] as $method)
		{
			$name = $method['ATTRIBUTES']['NAME'];
			if (!isset($foundRates[$name]))
			{
				$el = new SimpleXMLElement('<result></result>');
				$el->addAttribute('shipping-name', $name);
				$el->addAttribute('address-id', $addressID);

				$sRate = $el->addChild('shipping-rate');
				$sRate->addAttribute('currency', 'USD');
				$this->xmlValue($sRate, 0);

				$sRate = $el->addChild('shippable');
				$this->xmlValue($sRate, 'false');

				$this->xmlAppend($results, $el);
			}
		}

		$response = new SimpleXMLElement('<merchant-calculation-results></merchant-calculation-results>');
		$response->addAttribute('xmlns', 'http://checkout.google.com/schema/2');
		$this->xmlAppend($response, $results);

		return new SimpleXMLResponse($response);
	}

	private function createOrder($array)
	{
		$cart = $array['SHOPPING-CART'][0]['ITEMS'][0]['ITEM'];
		$orderID = $cart[0]['MERCHANT-PRIVATE-ITEM-DATA'][0]['ORDER-ID'][0]['VALUE'];

		$gcIDs = $prices = array();
		foreach ($cart as $item)
		{
			if (!isset($item['MERCHANT-PRIVATE-ITEM-DATA']))
			{
				continue;
			}
			$itemID = $item['MERCHANT-PRIVATE-ITEM-DATA'][0]['ITEM-ID'][0]['VALUE'];
			$gcIDs[$itemID] = true;
			$prices[$itemID] = $item['UNIT-PRICE'][0];
		}

		$order = CustomerOrder::getInstanceByID($orderID, true);
		$order->loadAll();

		// remove items that are not in Google cart
		foreach ($order->getOrderedItems() as $item)
		{
			if (!isset($gcIDs[$item->getID()]))
			{
				$order->removeItem($item);
			}
		}

		return $order;
	}

	private function getShippingRates(CustomerOrder $order, $addresses)
	{
		if (!$order->isShippingRequired())
		{
			return array();
		}

		$shipment = $order->getShipments()->get(0);
		$rates = array();
		foreach ($addresses as $id => $address)
		{
			$order->shippingAddress->set($address);
			$tax = $order->getTaxAmount();
			$rates[$id] = $shipment->getShippingRates()->toArray();
			foreach ($rates[$id] as &$rate)
			{
				$rate['orderTax'] = $tax;
			}
		}

		$order->shippingAddress->set(null);

		return $rates;
	}

	private function getAddresses($array)
	{
		$addresses = array();
		foreach ($array['CALCULATE'][0]['ADDRESSES'][0]['ANONYMOUS-ADDRESS'] as $address)
		{
			$ua = UserAddress::getNewInstance();
			foreach (array('COUNTRY-CODE' => 'countryID', 'POSTAL-CODE' => 'postalCode', 'CITY' => 'city', 'REGION' => 'stateName') as $google => $livecart)
			{
				$ua->$livecart->set($address[$google][0]['VALUE']);
			}

			// state lookup
			if ($stateID = State::getStateIDByName($ua->countryID->get(), $ua->stateName->get()))
			{
				$ua->state->set(State::getInstanceByID($stateID, true));
			}

			$ua->countryID->set($address['COUNTRY-CODE'][0]['VALUE']);
			$addresses[$address['ATTRIBUTES']['ID']] = $ua;
		}

		return $addresses;
	}

	private function getPostData()
	{
		$fp = fopen("php://input", "r");
		$input = '';
		while(!feof($fp))
		{
			$input .= fread($fp, sizeof($fp));
		}
		fclose($fp);

		file_put_contents('/tmp/gc', $input);

		$array = $this->getArrayFromXML($input);

		return $array;
	}

	private function getArrayFromXML($xml)
	{
		$parser = new xmlparser();
		return $parser->GetXMLTree($xml);
	}

	function xmlAppend(SimpleXMLElement $parent, SimpleXMLElement $new_child)
	{
	   $node1 = dom_import_simplexml($parent);
	   $dom_sxe = dom_import_simplexml($new_child);
	   $node2 = $node1->ownerDocument->importNode($dom_sxe, true);
	   $node1->appendChild($node2);
	}

	function xmlValue(SimpleXMLElement $parent, $text)
	{
		$node1 = dom_import_simplexml($parent);
		$node1->appendChild(new DOMText($text));
	}

}

?>