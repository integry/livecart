<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class InternetSecureMerchantLink extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		// vendor account number.
		$params['GatewayID'] = $this->getConfigValue('account');

		// a unique order id from your program. (128 characters max)
		$params['xxxVar1'] = $this->details->invoiceID->get();

		// the total amount to be billed, in decimal form, without a currency symbol.
		$params['total'] = $this->details->amount->get();

		$params['CancelURL'] = $this->cancelUrl;
		$params['ReturnCGI'] = $this->notifyUrl;

		// customer information
		$params['xxxName'] = $this->details->getName();
		$params['xxxAddress'] = $this->details->address->get();
		$params['xxxCity'] = $this->details->city->get();
		$params['xxxProvince'] = $this->details->state->get();
		$params['xxxPostal'] = $this->details->postalCode->get();
		$params['xxxCountry'] = $this->details->country->get();
		$params['xxxEmail'] = $this->details->email->get();
		$params['xxxPhone'] = $this->details->phone->get();

		// flags
		$flags = '';
		if ($this->getConfigValue('test'))
		{
			$flags .= '{TEST}';
		}

		if ('USD' == $this->details->currency->get())
		{
			$flags .= '{USD}';
		}

		// product string
		$items = array(array('Price', 'Qty', 'Code', 'Description', 'Flags'));
		foreach ($this->details->getLineItems() as $item)
		{
			$items[] = array($item['price'], $item['quantity'], $item['sku'], $item['name'], $flags);
		}

		$lineItems = array();
		foreach ($items as &$item)
		{
			foreach ($item as &$el)
			{
				$el = substr($el, 0, 150);
				$el = str_replace(array('"', '`'), "'", $el);
				$el = str_replace(':', '-', $el);
			}

			$lineItems[] = implode('::', $item);
		}

		$params['Products'] = implode('|', $lineItems);

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://secure.internetsecure.com/process.cgi?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		// test transactions enabled?
		if (($requestArray['Live'] != 1) && !$this->getConfigValue('test'))
		{
			return new TransactionError('Test transactions disabled', $requestArray);
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['receiptnumber']);
		$result->amount->set($requestArray['xxxAmount']);
		$result->currency->set(0 == $requestArray['Currency'] ? 'CAD' : 'USD');
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['xxxVar1'];
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, array('CAD', 'USD')) ? $currentCurrencyCode : 'CAD';
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