<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class TwoCheckout extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		// your 2checkout vendor account number.
		$params['sid'] = $this->getConfigValue('account');

		// a unique order id from your program. (128 characters max)
		$params['cart_order_id'] = $this->details->invoiceID->get();

		// Specify your order number with this parameter. It will also be included in the confirmation emails to yourself and the customer. (50 characters max)
		$params['merchant_order_id'] = $this->details->invoiceID->get();

		// the total amount to be billed, in decimal form, without a currency symbol.
		$params['total'] = $this->details->amount->get();

		// Y to remove the Continue Shopping button and lock the quantity fields to 1
		$params['fixed'] = 'Y';

		// CC for Credit Card, CK for check, BML for BillMeLater, ATM for ATMDirect, or FXS for FXSource. This will select the payment method during the checkout process.
		$params['pay_method'] = 'CC';

		$params['return_url'] = $this->siteUrl;
		$params['complete_url'] = $this->returnUrl;
		//$params['lang'] = 'es_la';

		// customer information
		$params['card_holder_name'] = $this->details->getName();
		$params['street_address'] = $this->details->address->get();
		$params['city'] = $this->details->city->get();
		$params['state'] = $this->details->state->get();
		$params['zip'] = $this->details->postalCode->get();
		$params['country'] = $this->details->country->get();
		$params['email'] = $this->details->email->get();
		$params['phone'] = $this->details->phone->get();

		// test transaction?
		if ($this->getConfigValue('test'))
		{
			$params['demo'] = 'Y';
		}

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://www2.2checkout.com/2co/buyer/purchase?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
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
		return true;
	}

	public function get2CoCurrency()
	{
		return $this->getValidCurrency($this->getConfigValue('currency'));
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, array('AUD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'HKD', 'JPY', 'NOK', 'NZD', 'SEK', 'USD')) ? $currentCurrencyCode : 'USD';
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