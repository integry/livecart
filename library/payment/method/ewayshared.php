<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class ewayshared extends ExternalPayment
{
	private $fields = array();

	public function getUrl()
	{
		$this->addField('CustomerID', $this->getConfigValue('customerID'));
		$this->addField('UserName', $this->getConfigValue('userName'));

		$this->addField('Amount', number_format($this->details->amount->get(), 2, '.', ''));
		$this->addField('Currency', $this->details->currency->get());

		// customer information
		$this->addField('CustomerEmail', $this->details->email->get());
		$this->addField('CustomerFirstName', $this->details->firstName->get());
		$this->addField('CustomerLastName', $this->details->lastName->get());

		$this->addField('CustomerAddress', $this->details->address->get());
		$this->addField('CustomerCity', $this->details->city->get());
		$this->addField('CustomerState', $this->details->state->get());
		$this->addField('CustomerPostcode', $this->details->postalCode->get());
		$this->addField('CustomerCountry', $this->details->country->get());
		$this->addField('MerchantOption1', $this->returnUrl);
		$this->addField('MerchantOption2', $this->details->currency->get());
		$this->addField('MerchantOption3', '');
		$this->addField('InvoiceDescription', $this->getConfigValue('invoiceDescription'));

		// order information
		$this->addField('MerchantReference', $this->details->invoiceID->get());

		$this->addField('CancelUrl', $this->siteUrl);
		$this->addField('ReturnUrl', $this->notifyUrl);

		preg_match('/URI>(.*)<\/URI>/', $this->fetchUrl($this->getRequestUrl()), $match);
		return array_pop($match);
	}

	private function getRequestUrl($type = 'Request')
	{
		switch ($this->getConfigValue('country'))
		{
			case 'EWAY_UK':
				$paymentUrl = 'https://payment.ewaygateway.com/';
			break;

			case 'EWAY_NZ':
				$paymentUrl = 'https://nz.ewaygateway.com/';
			break;
		}

		$pairs = array();
		foreach ($this->fields as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return $paymentUrl . $type . '/?' . implode('&', $pairs);
	}

	private function addField($field, $value)
	{
		$this->fields[$field] = $value;
	}

	private function fetchUrl($url)
	{
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$content = curl_exec($ch);
			curl_close($ch);
		}
		else
		{
			$content = file_get_contents($url);
		}

		return $content;
	}

	private function getTransactionResultData($accessCode)
	{
		if (!empty($this->transactionResult))
		{
			return $this->transactionResult;
		}

		$this->addField('CustomerID', $this->getConfigValue('customerID'));
		$this->addField('UserName', $this->getConfigValue('userName'));
		$this->addField('AccessPaymentCode', $accessCode);

		foreach ($this->fields as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		$xml = simplexml_load_string($this->fetchUrl($this->getRequestUrl('Result')));

		$this->transactionResult = $xml;

		return $xml;
	}

	public function notify($requestArray)
	{
		if (in_array($this->transactionResult->ResponseCode, array('00', '08', '10', '11', '16')))
		{
			$result = new TransactionResult();
			$result->gatewayTransactionID->set((string)$this->transactionResult->AuthCode);
			$result->amount->set((string)$this->transactionResult->ReturnAmount);
			$result->currency->set((string)$this->transactionResult->MerchantOption2);
			$result->rawResponse->set($this->transactionResult);
			$result->setTransactionType(TransactionResult::TYPE_SALE);

			return $result;
		}
		else
		{
			return new TransactionError($this->transactionResult->ErrorMessage, $requestArray);
		}
	}

	public function getOrderIdFromRequest($requestArray)
	{
		$result = $this->getTransactionResultData($requestArray['AccessPaymentCode']);

		return (string)$result->MerchantReference;
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return (string)$this->transactionResult->MerchantOption1;
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getCurrency()
	{
		switch ($this->getConfigValue('country'))
		{
			case 'EWAY_UK':
				return 'GBP';
			break;

			case 'EWAY_NZ':
				return 'NZD';
			break;
		}
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