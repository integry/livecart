<?php
//  Payment processor for Auriga ePayment
//
//  by Greger Andersson, Electrokit Sweden AB
//	- for free use with LiveCart

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class AurigaePayment extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();
		// merchant id
		$params['Merchant_id'] = $this->getConfigValue('merchant');
		// version
		$params['Version'] = "3";		
		// A seller reference number for a transaction
		$params['Customer_refno'] = $this->details->invoiceID->get();//.mt_rand();
		// The currency code of the payment amount.
		$params['Currency'] = $this->details->currency->get();
		// The payment amount
		$params['Amount'] = round($this->details->amount->get() * 100);	

		$params['VAT'] = "0";
		// The payment method
		$params['Payment_method'] = strtoupper(trim($this->getConfigValue('method')));
		// Purchase date
		// $params['Purchase_date'] = date("YmdHi");
		
		$this->notifyUrl = preg_replace('/currency\=[A-Z]{3}/', '', $this->notifyUrl);     // remove currency from base URL
		$params['Response_URL'] = $this->notifyUrl;
		$params['Cancel_URL'] = $this->siteUrl;
		$params['Goods_description'] = "Order " . $this->details->invoiceID->get();	
		$params['Language'] = "SWE"; // ActiveRecordModel::getApplication()->getLocaleCode();		
		$params['Comment'] = '';
		$params['Country'] = $this->details->country->get();

		if ($this->getConfigValue('test'))
		{
			$params['Amount'] = 50001; // other values may not work in test mode.
			$params['Currency'] = 'SEK'; // for testing in shop w/o sek
			$params['Language'] = "ENG";
			$params['Country'] = "SE";
		}
		
		$params['MAC'] = md5(
			$params['Merchant_id'] .
			$params['Version'] .
			$params['Customer_refno'] . 
			$params['Currency'] .
			$params['Amount'] .
			$params['VAT'] .
			$params['Payment_method'] . 
			// $params['Purchase_date'] .
			$params['Response_URL'] .
			$params['Goods_description'] .
			$params['Language'] . 
			$params['Comment'] .
			$params['Country'] . 
			$params['Cancel_URL'] . 
			$this->getConfigValue('md5')
		);
		return $this->urlAppendParams(
			$this->getConfigValue('test')
				? 'https://test-epayment.auriganet.eu/paypagegw'
				: 'https://epayment.auriganet.eu/paypagegw',
			$params
		);
	}

	public function notify($requestArray)
	{
		// file_put_contents(ClassLoader::getRealPath('cache.') . 'notify.php', var_export($requestArray, true));
		// pp($requestArray);
		$string = array();
		foreach(array(
			'Merchant_id',
			'Version',
			'Customer_refno',
			'Transaction_id',
			'Status',
			'Status_code',
			'AuthCode',
			'3DSec',
			'Batch_id',
			'Currency',
			'Payment_method',
			'Card_num',
			'Exp_date',
			'Card_type',
			'Risk_score',
			'Ip_country',
			'Issuing_country',
			'Authorized_amount',
			'Fee_amount') as $field)
		{
			if(array_key_exists($field, $requestArray)) // not all keys are returned when transaction failed/denied.
			{
				$string[] = $requestArray[$field];
			}
		}
		$string[] = $this->getConfigValue('md5');
		if ($requestArray['MAC'] !== md5(implode('', $string)))
		{
			return new TransactionError('md5key mismatch', $requestArray);
		}
		if ($requestArray['Status'] != 'A' || $requestArray['Status_code'] != '0')
		{
			return new TransactionError('Transaction error/denied', $requestArray);
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['Transaction_id']);
		$result->rawResponse->set($requestArray);
		$result->amount->set($this->details->amount->get());			// amount not returned in response

		// just for testing in shop w/o SEK
		$requestArray['Currency'] = 'USD';

		$result->currency->set($requestArray['Currency']);
		$result->setTransactionType(TransactionResult::TYPE_AUTH);

	//	pp('ok',$requestArray, $result);
		
		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['Customer_refno'];
	}

	public function isPostRedirect()   // POST or GET
	{
		return false;
	}

	public function isNotify()
	{
		return true;
	}


	public function isHtmlResponse()
	{
		return false;
	}
	
	private function getCurrency($code)
	{
		return $code;
	}
	
	public static function getSupportedCurrencies()
	{
		return array('SEK'); // ?
		// return array('CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'NOK', 'SEK', 'USD');
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : 'USD';
	}

	public function isVoidable()
	{
		// pp('isVoidable called');
		return true;
	}

	public function void()
	{
		return $this->process('Annul');
	}
	
	public function capture()
	{
		return $this->process('Confirm');
	}
	
	public function process($Request_type)
	{
		$params = array();
		$params['Merchant_id'] = $this->getConfigValue('merchant');
		$params['Version'] = '3';
		$params['Customer_refno'] = $this->order->getID();
		
		$params['Transaction_id'] = $this->details->gatewayTransactionID->get();
		// The payment amount
		
		$params['Amount'] = round($this->details->amount->get() * 100);	
		
		if ($this->getConfigValue('test'))
		{
			$params['Amount'] = 50001; // other values may not work in test mode.
		}

		$params['VAT'] = "0";

		$params['Response_URL'] = $this->application->getRouter()->getBaseUrl();
		$params['Request_type'] = $Request_type;
		$params['Delivery_date'] = date("Ymd"); // yyyymmdd
		$params['MAC'] = md5(
			$params['Merchant_id'].
			$params['Version'].
			$params['Customer_refno'].
			$params['Transaction_id'].
			$params['Amount'].
			$params['VAT'].
			$params['Response_URL'].
			$params['Request_type'].
			$params['Delivery_date'].
			$this->getConfigValue('md5')
		);
		$path = $this->urlAppendParams(
			$this->getConfigValue('test')
				? 'https://test-epayment.auriganet.eu/admingw'
				: 'https://epayment.auriganet.eu/admingw',
			$params
		);
		$ch = curl_init($path);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = urldecode(curl_exec($ch));
		$requestArray = array();
		if(preg_match('/Location\:.*/', $response, $m)) // where aurigaePayment want to redirect? get from response headers.
		{
			list($location, $parameterString) = explode($params['Response_URL'].'?', $m[0]);
			$pairs = explode('&', $parameterString);
			foreach($pairs as $pair)
			{
				list($key, $value) = explode('=',$pair);
				$requestArray[trim($key)] = trim($value);
			}
		}

		// mac check
		$string = array();
		foreach(array(
			'Merchant_id',
			'Version',
			'Customer_refno',
			'Transaction_id',
			'Status',
			'Status_code',
			'Credit_status_code',
			'AuthCode',
			'3DSec',
			'Batch_id',
			'Currency',
			'Payment_method',
			'Card_num',
			'Exp_date',
			'Card_type',
			'Risk_score',
			'Issuing_bank',
			'Ip_country',
			'Issuing_country',
			'Authorized_amount',
			'Fee_amount',
			'Credit_amount') as $field)
		{
			if(array_key_exists($field, $requestArray)) // not all keys are returned when failed/denied.
			{
				$string[] = $requestArray[$field];
			}
		}
		$string[] = $this->getConfigValue('md5');
		if ($requestArray['MAC'] !== md5(implode('', $string)))
		{
			return new TransactionError('md5key mismatch', $requestArray);
		}
		// --
		if ($requestArray['Status'] !== 'A' || $requestArray['Status_code'] != '0')
		{
			return new TransactionError('Transaction error/denied', $requestArray);
		}

		if ($this->getConfigValue('test'))
		{
			$requestArray['Currency'] = 'USD'; // for testing in shop w/o sek, changing back to usd
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['Transaction_id']);
		$result->rawResponse->set($requestArray);
		$result->amount->set($this->details->amount->get());
		$result->currency->set($requestArray['Currency']);
		$result->setTransactionType(TransactionResult::TYPE_CAPTURE);

		return $result;
	}

	private function urlAppendParams($url, $params)
	{
		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . (($value));
		}
		return $url.'?'. implode('&', $pairs);
	}
}

?>