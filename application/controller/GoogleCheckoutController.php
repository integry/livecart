<?php


/**
 *  Handles Google Checkout callbacks
 *
 * @author Integry Systems
 * @package application/controller
 */
class GoogleCheckoutController extends CheckoutController
{
	public function indexAction()
	{
		$handler = $this->application->getPaymentHandler('GoogleCheckout')->getHandler();
		$array = $origArray = $this->getPostData();

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
		$order = $this->createorderBy($array);
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
			$address = $order->billingAddress;
			$user = User::getNewInstance($email);
			foreach (array('firstName', 'lastName', 'companyName') as $field)
			{
				$user->$field->set($address->$field);
			}

			$user->save();
		}

		$order->setUser($user);
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
		if ($stateID = State::getStateIDByName($ua->countryID, $ua->stateName))
		{
			$ua->state->set(State::getInstanceByID($stateID, true));
		}

		$ua->countryID->set($address['COUNTRY-CODE'][0]['VALUE']);

		$names = explode(' ', $ua->firstName, 2);
		$ua->firstName->set(array_shift($names));
		$ua->lastName->set(array_shift($names));

		$ua->save();

		return $ua;
	}

	private function calculationCallback($array)
	{
		$order = $this->createorderBy($array);
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

	private function createorderBy($array)
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
		$order->setPaymentMethod('GoogleCheckout');
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
			if ($stateID = State::getStateIDByName($ua->countryID, $ua->stateName))
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