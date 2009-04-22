<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class Ogone extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		// Your affiliation name in our system, chosen by yourself when opening your account with us. This is a unique identifier and can’t ever be changed.
		$params['PSPID'] = $this->getConfigValue('pspid');

		// a unique order id from your program. (128 characters max)
		$params['orderID'] = $this->details->invoiceID->get();

		// the total amount to be billed, in decimal form, without a currency symbol.
		$params['amount'] = $this->details->amount->get() * 100;

		$params['currency'] = $this->details->currency->get();

		$params['accepturl'] = $this->notifyUrl;
		$params['accepturl'] = $this->returnUrl;

		$params['cancelurl'] = $this->cancelUrl;
		$params['catalogurl'] = $this->siteUrl;
		$params['declineurl'] = $this->cancelUrl;
		$params['exceptionurl'] = $this->cancelUrl;
		$params['homeurl'] = $this->siteUrl;

		// customer information
		$params['CN'] = $this->details->getName();
		$params['owneraddress'] = $this->details->address->get();
		$params['ownertown'] = $this->details->city->get();
		$params['state'] = $this->details->state->get();
		$params['ownerZIP'] = $this->details->postalCode->get();
		$params['ownercty'] = $this->details->country->get();
		$params['EMAIL'] = $this->details->email->get();
		$params['ownertelno'] = $this->details->phone->get();

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://secure.ogone.com/ncol/' . ($this->getConfigValue('test') ? 'test' : 'prod') . '/orderstandard.asp?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		$this->saveDebug($requestArray);
		if ($secretWord = $this->getConfigValue('secretWord'))
		{
			// Check the SHA1 signature
			$format = '%s%s%s%s%s%s%s%s%s%s%s';

			$shastr = sprintf( $format,
			  $requestArray['orderID'],
			  $requestArray['currency'],
			  $requestArray['amount'],
			  $requestArray['PM'],
			  $requestArray['ACCEPTANCE'],
			  $requestArray['STATUS'],
			  $requestArray['CARDNO'],
			  $requestArray['PAYID'],
			  $requestArray['NCERROR'],
			  $requestArray['BRAND'],
			  // Point 4.4 of technical information
			  $secretWord);

			$Sha1 = strtoupper(sha1($shastr));

			// Ensures that no returning parameter has been altered
			if ($Sha1 != $requestArray['SHASIGN'])
			{
				return new TransactionError('SHASIGN mismatch' , $requestArray);
			}
		}

		// Check payment authorization
		if (!in_array($requestArray['STATUS'], array(5, 51, 59, 9, 91, 95)))
		{
			return new TransactionError('Payment Not Authorized. Status = '.$requestArray['STATUS'], $requestArray);
		}

		// Transaction OK
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['orderID']);
		$result->amount->set($requestArray['amount']);
		$result->currency->set($requestArray['currency'] );
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['orderID'];
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
		return in_array($currentCurrencyCode, array('AED', 'AUD', 'CAD', 'CHF', 'CNY', 'CYP', 'CZK', 'DKK', 'EEK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'ILS', 'ISK', 'JPY', 'LTL', 'LVL', 'MAD', 'MTL', 'MXN', 'NOK', 'NZD', 'PLN', 'RUR', 'SEK', 'SGD', 'SKK', 'THB', 'TRY', 'UAH', 'USD', 'XAF', 'XOF', 'ZAR')) ? $currentCurrencyCode : 'USD';
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