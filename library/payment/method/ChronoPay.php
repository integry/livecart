<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class ChronoPay extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		$params['cs1'] = $this->details->invoiceID->get();

		// Unique code of product or service. ChronoPay uses it to determine which Merchant site Customer belongs to
		$params['product_id'] = $this->getConfigValue('productid');
		$params['product_price'] = $this->details->amount->get();
		$params['product_name'] = $this->getConfigValue('productname');

		$params['decline_url'] = $this->siteUrl;
		$params['cb_url'] = $this->notifyUrl;

		// customer information
		$params['f_name'] = $this->details->firstName->get();
		$params['s_name'] = $this->details->lastName->get();
		$params['street'] = $this->details->address->get();
		$params['city'] = $this->details->city->get();
		$params['state'] = $this->details->state->get();
		$params['zip'] = $this->details->postalCode->get();
		$params['country'] = $this->getChronoPayCountryCode($this->details->country->get());
		$params['email'] = $this->details->email->get();
		$params['phone'] = $this->details->phone->get();

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://secure.chronopay.com/index_shop.cgi?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		if ('69.20.58.35' != $_SERVER['REMOTE_ADDR'])
		{
			exit;
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['transaction_id']);
		$result->amount->set($requestArray['total']);
		$result->currency->set($requestArray['currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['cs1'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return $requestArray['complete_url'];
	}

	public function isHtmlResponse()
	{
		return false;
	}

	public function get2CoCurrency()
	{
		return $this->getValidCurrency($this->getConfigValue('currency'));
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return $this->getConfigValue('currency');
	}

	public function getChronoPayCountryCode($twoLetterCode)
	{
		return $this->get3LetterCountryCode($twoLetterCode);
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