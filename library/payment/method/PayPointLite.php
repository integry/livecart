<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class PayPointLite extends ExternalPayment
{
	// By default, all transactions sent to PayPoint.net are assumed to be GBP
	const PAYPOINTLITE_DEFAULT_CURRENCY = 'GBP';
	
	public function getUrl()
	{
		$params = array();
		// This is your PayPoint.net username (usually six letters followed by two numbers).
		$params['merchant'] = $this->getConfigValue('merchant');
		// A unique transaction identifier created by yourself.
		$params['trans_id'] = $this->details->invoiceID->get();
		// This is the amount for the transaction
		$params['amount'] = str_replace(',','.',$this->details->amount->get());
		$params['callback'] = $this->notifyUrl;
		// trans_idamountremotepassword encrypted to md5
		$params['digest'] = md5($params['trans_id'].$params['amount'].$this->getConfigValue('remotepassword'));

		// customer information
		$params['bill_name'] = $this->details->getName();
		$params['bill_company'] = $this->details->companyName->get();
		$params['bill_addr_1'] = $this->details->address->get();
		//$params['bill_addr_2'] = ??
		$params['bill_city'] = $this->details->city->get();
		if($params['bill_city'] instanceof City)
		{
			$params['bill_city'] = $this->details->city->get()->name->get();
		}
		$params['bill_state'] = $this->details->state->get();
		$params['bill_country'] = $this->details->country->get();
		$params['bill_post_code'] = $this->details->postalCode->get();
		$params['bill_tel'] = $this->details->phone->get();
		$params['bill_email'] = $this->details->email->get();
		//  bill_url
		$params['ship_name'] = $this->details->shippingFirstName->get(). ' '.$this->details->shippingLastName->get();
		$params['ship_company'] = $this->details->shippingCompanyName->get();
		$params['ship_addr_1'] = $this->details->shippingAddress->get();
		// ship_addr_2
		
		$params['ship_city'] = $this->details->shippingCity->get();
		if($params['ship_city'] instanceof City)
		{
			$params['ship_city'] = $this->details->shippingCity->get()->name->get();
		}
		$params['ship_state'] = $this->details->shippingState->get();
		$params['ship_country'] = $this->details->shippingCountry->get();
		$params['ship_post_code'] = $this->details->shippingPostalCode->get();
		$params['ship_tel'] = $this->details->shippingPhone->get();
		$params['ship_email'] = $this->details->shippingEmail->get();
		// ship_url

		// order

		// $xml = new SimpleXMLElement("<order class='com.secpay.seccard.Order'><orderLines class='com.secpay.seccard.OrderLine'></orderLines></order>");
		// $xmlOrderLines = $xml->xpath('/order/orderLines');
		// $xmlOrderLines=$xmlOrderLines[0];
		$items = array();
		foreach ($this->details->getLineItems() as $item)
		{
			// $xmlOrderLine = $xmlOrderLines->addChild('OrderLine');
			// $xmlElement = $xmlOrderLine->addChild('prod_code');
			// $xmlElement[0] = $item['sku'].' '.$item['name'];
    		// $xmlElement = $xmlOrderLine->addChild('item_amount', $item['price']);
			// $xmlElement[0] = $item['price'];
			// $xmlElement = $xmlOrderLine->addChild('quantity', $item['quantity']);
			// $xmlElement[0] = $item['quantity'];

			$items[] = sprintf('prod=%s,item_amount=%0.2f*%d',
				str_replace(
					array(',','=',';','*'), // are used as wildcards, data can't contain them.
					array('.',' ','|',' '), $item['sku'].' '.$item['name']),
				$item['price'],
				$item['quantity']
			);
		}
		$params['order'] = 'delimit=;,=*;'.implode(';', $items);
		// $params['order'] = $xml->asXML();
		// $params['order_2'] = "<order class='com.secpay.seccard.Order'><orderLines class='com.secpay.seccard.OrderLine'><OrderLine><prod_code>funny_book</prod_code><item_amount>18.50</item_amount><quantity>1</quantity></OrderLine><OrderLine><prod_code>scary_book</prod_code><item_amount>10.00</item_amount><quantity>5</quantity></OrderLine></orderLines></order>";
		// list($ignore, $params['order']) = explode('?'.'>', $params['order'],2);
		// $params['order'] = trim(str_replace(array('"', "\t", "\n", "\r", "\0", "\x0B"), ' ', $params['order']));
		
		$currency = $this->details->currency->get();
		if($currency != self::PAYPOINTLITE_DEFAULT_CURRENCY)
		{
			$params['currency'] = $currency;
		}
		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . (/*$key == 'order' ? $value :*/ urlencode($value));
		}
		// pp($pairs);
		return 'https://www.secpay.com/java-bin/ValCard?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		if($requestArray['valid'] == 'false')
		{
			return new TransactionError('Transaction declined', $requestArray);
		}
		// 1. Remove the domain
		$callbackStr = $_SERVER['REQUEST_URI'];
		// 2. Remove the hash parameter
		$callbackStr = substr($callbackStr, 0, strrpos($callbackStr,'&hash=')+1 );
		// 4. Append your secret digest key		
		$callbackStr .= $this->getConfigValue('digestkey');
		// 5. Encrypt using MD5
		$callbackStr = md5($callbackStr);
		// 6. compare it with the version sent by PayPoint.net
		if($requestArray['hash'] != $callbackStr)
		{
			return new TransactionError('Invalid hash', $requestArray);
		}
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['trans_id']);
		$result->amount->set($requestArray['amount']);
		$result->currency->set(
		    // only supplied when a currency other than the default is used
			array_key_exists('currency', $requestArray) ? $requestArray['currency'] : self::PAYPOINTLITE_DEFAULT_CURRENCY
		);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['trans_id'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return null;
		/*
		$router = $this->application->getRouter();
		return $router->createFullUrl($router->createUrl(
			array('controller' => 'checkout', 'action' => 'completeExternal', 'id' => $requestArray['trans_id'])));
	    */
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return $currentCurrencyCode;
	}

	public function isVoidable()
	{
		return false;
	}

	public function void()
	{
		return false;
	}
}
?>