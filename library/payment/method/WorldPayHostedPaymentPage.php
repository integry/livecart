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
		$this->saveDebug($requestArray);
		exit;

		// check for secret word
		if ($secretWord = $this->getConfigValue('secretWord'))
		{
			$orderNum = 'Y' == $requestArray['demo'] ? 1 : $requestArray['order_number'];
			$expected = $secretWord . $requestArray['sid'] . $orderNum . $requestArray['total'];
			if ($requestArray['key'] != strtoupper(md5($expected)))
			{
				return new TransactionError('Invalid 2Checkout secret word', $requestArray);
			}
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['order_number']);
		$result->amount->set($requestArray['total']);
		$result->currency->set($this->get2CoCurrency());
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['merchant_order_id'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return $requestArray['complete_url'];
	}

	public function isHtmlResponse()
	{
		return false;
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