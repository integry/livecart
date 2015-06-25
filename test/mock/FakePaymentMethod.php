<?php

ClassLoader::import('library.payment.abstract.ExternalPayment');

/**
*  Simulates a payment method
*
*  To be used when testing checkout process
*
* @author Shumoapp
* @package test.mock
*/
class FakePaymentMethod extends ExternalPayment
{
	/**
	 *	Return payment page URL
	 */
	public function getUrl()
	{
		return '';
	}

	/**
	 *	Payment confirmation post-back
	 */
	public function notify($requestArray)
	{
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['transactionId']);
		$result->amount->set($requestArray['amount']);
		$result->currency->set($requestArray['currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	/**
	 *	Extract order ID from payment gateway response data
	 */
	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['orderID'];
	}

	/**
	 *	Determine if HTML output is required as post-notification response
	 *  @return bool
	 */
	public function isHtmlResponse()
	{
		return false;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		$defCurrency = $this->getConfigValue('DEF_CURRENCY');
		if (!$defCurrency)
		{
			$defCurrency = 'USD';
		}
		return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : $defCurrency;
	}

	public static function getSupportedCurrencies()
	{
		return array('USD', 'CAD', 'EUR', 'GBP', 'JPY', 'AUD', 'NZD', 'CHF', 'HKD', 'SGD', 'SEK', 'DKK', 'PLN', 'NOK', 'HUF', 'CZK', 'MXN', 'ILS');
	}

	public function isVoidable()
	{
		return false;
	}
}
