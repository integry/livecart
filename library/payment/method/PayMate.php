<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class PayMate extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		// user name
		$params['mid'] = $this->getConfigValue('mid');

		// A seller reference number for a transaction
		$params['ref'] = $this->details->invoiceID->get();

		// The payment amount
		$params['amt'] = $this->details->amount->get();
		$params['amt_editable'] = 'N';

		// The currency code of the payment amount.
		$params['currency'] = $this->details->currency->get();

		$params['return'] = $this->notifyUrl;

		// customer information
		$params['pmt_contact_firstname'] = $this->details->firstName->get();
		$params['pmt_contact_surname'] = $this->details->lastName->get();
		$params['regindi_address1'] = $this->details->address->get();
		$params['regindi_sub'] = $this->details->city->get();
		$params['regindi_state'] = $this->details->state->get();
		$params['regindi_pcode'] = $this->details->postalCode->get();
		$params['pmt_country'] = $this->details->country->get();
		$params['pmt_sender_email'] = $this->details->email->get();
		$params['pmt_contact_phone'] = $this->details->phone->get();

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://www.paymate.com/PayMate/' . ($this->getConfigValue('test') ? 'Test' : '') . 'ExpressPayment?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		if ('PD' == $requestArray['responseCode'])
		{
			return new TransactionError('Transaction declined', $requestArray);
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['transactionID']);
		$result->amount->set(str_replace(',', '', $requestArray['paymentAmount']));
		$result->currency->set($requestArray['currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['ref'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return $requestArray['complete_url'];
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, array('AUD', 'EUR', 'GBP', 'NZD', 'USD')) ? $currentCurrencyCode : 'USD';
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