<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class WorldPayHostedPaymentPage extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		$params['instId'] = $this->getConfigValue('account');

		$params['cartId'] = $this->details->invoiceID->get();

		$params['amount'] = $this->details->amount->get();
		$params['currency'] = $this->details->currency->get();

		// customer information
		$params['name'] = $this->details->getName();
		$params['address'] = $this->details->city->get() . "\n" . $this->details->state->get() . "\n" . $this->details->address->get();
		$params['postcode'] = $this->details->postalCode->get();
		$params['country'] = $this->details->country->get();
		$params['email'] = $this->details->email->get();
		$params['tel'] = $this->details->phone->get();

		// test transaction?
		if ($this->getConfigValue('test'))
		{
			$params['testMode'] = '100';
		}

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://select.wp3.rbsworldpay.com/wcc/purchase?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['cartId']);
		$result->amount->set($requestArray['cost']);
		$result->currency->set($requestArray['currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['cartId'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return '';
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, array('ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'IDR', '0JPY', 'KES', 'KRW', 'MXP', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'PTE', 'SEK', 'SGD', 'SKK', 'THB', 'TWD', 'USD', 'VND', 'ZAR')) ? $currentCurrencyCode : 'USD';
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