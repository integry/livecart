<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class ProtxForm extends ExternalPayment
{
	public function getUrl()
	{
		switch ($this->getConfigValue('mode'))
		{
			case 'PROTX_LIVE': return 'https://ukvps.protx.com/vspgateway/service/vspform-register.vsp';
			case 'PROTX_TEST': return 'https://ukvpstest.protx.com/vspgateway/service/vspform-register.vsp';
			case 'PROTX_SIMULATOR': return 'https://ukvpstest.protx.com/VSPSimulator/VSPFormGateway.asp';
		}
	}

	public function getPostParams()
	{
		$main = array();

		$main['VPSProtocol'] = '2.22';
		$main['TxType'] = 'PAYMENT';
		$main['Vendor'] = $this->getConfigValue('vendor');

		$params = array();

		// the total amount to be billed, in decimal form, without a currency symbol.
		$params['Amount'] = number_format($this->details->amount->get(), 2, '.', '');
		$params['Currency'] = $this->details->currency->get();

		// This should be your own reference code to the transaction. Your site should
		// provide a completely unique VendorTxCode for each transaction.
		$params['VendorTxCode'] = $this->details->invoiceID->get() . '-' . rand(1, 100000) . '-' . $params['Currency'];

		$params['Description'] = $this->getConfigValue('description');

		$params['FailureUrl'] = $this->cancelUrl;
		$params['SuccessUrl'] = $this->notifyUrl;

		// customer information
		$params['CustomerName'] = $this->details->getName();
		$params['BillingAddress'] = $this->details->address->get() . "\n" . $this->details->city->get() . "\n" . $this->details->state->get() . "\n" . $this->details->country->get();
		$params['BillingPostCode'] = $this->details->postalCode->get();
		$params['CustomerEMail'] = $this->details->email->get();
		$params['ContactNumber'] = $this->details->phone->get();

		$main['Crypt'] = base64_encode($this->simpleXor($this->getParamsString($params, false), $this->getConfigValue('key')));

		return $main;
	}

	public function isPostRedirect()
	{
		return true;
	}

	private function getParamsString($params, $encode = true)
	{
		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . ($encode ? urlencode($value) : $value);
		}

		return implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		$data = $this->getReturnData($requestArray);
		if ('OK' == $data['Status'])
		{
			list($id, $rand, $currency) = explode('-', $data['VendorTxCode']);

			$result = new TransactionResult();
			$result->gatewayTransactionID->set($data['VPSTxId']);
			$result->amount->set($data['Amount']);
			$result->currency->set($currency);
			$result->rawResponse->set($data);
			$result->setTransactionType(TransactionResult::TYPE_SALE);

			$checks = array();
			foreach (array_intersect_key($data, array_flip(array('AVSCV2', 'AddressResult', 'PostCodeResult', 'CV2Result'))) as $check => $res)
			{
				$checks[] = $check . ': ' . $res;
			}

			$result->details->set(implode('<br>', $checks));
		}
		else
		{
			$result = new TransactionError($data['StatusDetail'], $requestArray);
		}

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		$data = $this->getReturnData($requestArray);
		if (!$data)
		{
			return 0;
		}
		list($id, $rand) = explode('-', $data['VendorTxCode']);
		return $id;
	}

	private function getReturnData($requestArray)
	{
		$str = $this->simpleXor($this->base64Decode($requestArray['crypt']), $this->getConfigValue('key'));

		$data = array();
		foreach (explode('&', $str) as $pair)
		{
			list($key, $value) = explode('=', $pair, 2);
			$data[$key] = $value;
		}

		return $data;
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

	/* Base 64 decoding function **
	** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/
	private function base64Decode($scrambled)
	{
	  return base64_decode(str_replace(" ","+",$scrambled));
	}

	/*  The SimpleXor encryption algorithm                                                                                **
	**  NOTE: This is a placeholder really.  Future releases of VSP Form will use AES or TwoFish.  Proper encryption      **
	**  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering        **
	**  It won't stop a half decent hacker though, but the most they could do is change the amount field to something     **
	**  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still          **
	**  more secure than the other PSPs who don't both encrypting their forms at all                                      */
	private function simpleXor($InString, $Key)
	{
	  // Initialise key array
	  $KeyList = array();
	  // Initialise out variable
	  $output = "";

	  // Convert $Key into array of ASCII values
	  for($i = 0; $i < strlen($Key); $i++){
		$KeyList[$i] = ord(substr($Key, $i, 1));
	  }

	  // Step through string a character at a time
	  for($i = 0; $i < strlen($InString); $i++) {
		// Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
		// % is MOD (modulus), ^ is XOR
		$output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
	  }

	  // Return the result
	  return $output;
	}
}

?>