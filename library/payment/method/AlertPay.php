<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class AlertPay extends ExternalPayment
{
	public function getUrl()
	{
		$url = 'https://www.alertpay.com/PayProcess.aspx';

		$params = array();

		$params['ap_merchant'] = $this->getConfigValue('EMAIL');
		$params['ap_itemname'] = $this->getConfigValue('ITEM_NAME');

		$params['ap_quantity'] = 1;

		$params['ap_amount'] = $this->details->amount->get();
		$params['ap_totalamount'] = $this->details->amount->get();
		$params['ap_currency'] = $this->details->currency->get();

		$params['apc_1'] = $this->details->invoiceID->get();

		$params['ap_custfirstname'] = $this->details->firstName->get();
		$params['ap_custlastname'] = $this->details->firstName->get();
		$params['ap_custaddress'] = $this->details->address->get();
		$params['ap_custcity'] = $this->details->city->get();
		$params['ap_custstate'] = $this->details->state->get();
		$params['ap_custzip'] = $this->details->postalCode->get();
		$params['ap_custcountry'] = $this->details->country->get();
		$params['ap_custemailaddress'] = $this->details->email->get();
		$params['ap_purchasetype'] = 'item';

		$params['ap_returnurl'] = $this->returnUrl;
		$params['ap_cancelurl'] = $this->cancelUrl;

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return $url . '?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		// check for secret word
		if ($requestArray['ap_securitycode'] != $this->getConfigValue('SECURITY_CODE'))
		{
			return new TransactionError('Invalid AlertPay security code', $requestArray);
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['ap_referencenumber']);
		$result->amount->set($requestArray['ap_amount']);
		$result->currency->set($requestArray['ap_currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['apc_1'];
	}

	public function isHtmlResponse()
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
		return array('AUD', 'BGN', 'CAD', 'CHF', 'CZK', 'DKK', 'EEK', 'EUR', 'GBP', 'HKD', 'HUF', 'LTL', 'MYR', 'NOK', 'NZD', 'PLN', 'RON', 'SEK', 'SGD', 'USD', 'ZAR');
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