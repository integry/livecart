<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
*
* @package library.payment.method
* @author Integry Systems
*/
class WebToPay extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		$params['merchantid'] = $this->getConfigValue('merchantid');
		$params['projectid'] = $this->getConfigValue('projectid');

		$params['orderid'] = $this->details->invoiceID->get();

		$params['amount'] = $this->details->amount->get() * 100;
		$params['currency'] = $this->details->currency->get();
		$params['country'] = $this->getConfigValue('country');
		$params['lang'] = ActiveRecordModel::getApplication()->getLocaleCode();
		$params['lang'] = 'LIT';

		$params['callbackurl'] = $this->notifyUrl;
		$params['accepturl'] = $this->returnUrl;
		$params['cancelurl'] = $this->cancelUrl;

		// customer information
		$params['p_firstname'] = $this->details->firstName->get();
		$params['p_lastname'] = $this->details->lastName->get();
		$params['p_street'] = $this->details->address->get();
		$params['p_city'] = $this->details->city->get();
		$params['p_state'] = $this->details->state->get();
		$params['p_postcode'] = $this->details->postalCode->get();
		$params['p_country'] = $this->details->country->get();
		$params['p_email'] = $this->details->email->get();
		$params['p_phone'] = $this->details->phone->get();

		if ($this->getConfigValue('test'))
		{
			$params['test'] = '1';
		}

		$params['payment'] = $params['logo'] = $params['p_zip'] = $params['p_countrycode'] = '';

		# -- Value --
		$arrFields = array(
			'merchantid' => $params['merchantid'],
			'orderid' => $params['orderid'],
			'lang' => $params['lang'],
			'amount' => $params['amount'],
			'currency' => $params['currency'],
			'accepturl' => $params['accepturl'],
			'cancelurl' => $params['cancelurl'],
			'callbackurl' => $params['callbackurl'],
			'payment' => $params['payment'],
			'country' => $params['country'],
			'logo' => $params['logo'],
			'p_firstname' => $params['p_firstname'],
			'p_lastname' => $params['p_lastname'],
			'p_email' => $params['p_email'],
			'p_street' => $params['p_street'],
			'p_city' => $params['p_city'],
			'p_state' => $params['p_state'],
			'p_zip' => $params['p_zip'],
			'p_countrycode' => $params['p_countrycode'],
			'test' => $params['test']
		);

		// -- Do sign --
		$data = '';

		foreach ($arrFields as $num => $value)
		{
			if (trim($value) != '')
			{
				$data .= sprintf("%03d", strlen($value)) . strtolower($value);
			}
		}

		$params['sign'] = md5($data . $this->getConfigValue('signaturepassword'));
		// -- Do sign, END --

		$pairs = array();

		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://www.webtopay.com/pay/?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		$this->cleanRequestData($requestArray);

		if (!$this->goodRequest($requestArray))
		{
			return new TransactionError('Bad _ss2 signature!', $requestArray);
		}

		if ($this->getConfigValue('merchantid') != $requestArray['merchantid'])
		{
//			return new TransactionError('Incorrect MerchantID!', $requestArray);
		}

		if ($this->getConfigValue('projectid') != $requestArray['projectid'])
		{
			return new TransactionError('Incorrect ProjectID!', $requestArray);
		}

		if ($requestArray['status'] != '1')
		{
			return new TransactionError('Status not accepted: ' . $requestArray['status'], $requestArray);
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['orderid']);
		$result->amount->set($requestArray['amount'] / 100);
		$result->currency->set($requestArray['currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		// -- Shop must return OK for our server --
		echo('OK');

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		$this->cleanRequestData($requestArray);
		return $requestArray['orderid'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return '';
	}

	public function isHtmlResponse()
	{
		return false;
	}

	/*
	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : 'LTL';
	}

	public static function getSupportedCurrencies()
	{
		return array('LTL', 'EUR', 'USD', 'LVL', 'EEK');
	}
	*/

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

	function getCert($cert = null)
	{
		$fp = fsockopen("downloads.webtopay.com", 80, $errno, $errstr, 30);
		if (!$fp)
		{
			exit("Cert error: $errstr ($errno)<br />\n");
		}
		else
		{
			$out = "GET /download/" . ($cert ? $cert : 'public.key') . " HTTP/1.1\r\n";
			$out .= "Host: downloads.webtopay.com\r\n";
			$out .= "Connection: Close\r\n\r\n";

			$content = '';

			fwrite($fp, $out);
			while (!feof($fp)) $content .= fgets($fp, 8192);
			fclose($fp);

			list($header, $content) = explode("\r\n\r\n", $content, 2);

			return $content;
		}
	}

	function checkCert($cert = null, $request)
	{
		$this->cleanRequestData($request);

		$pKeyP = $this->getCert($cert);

		if (!$pKeyP) return false;

		$pKey = openssl_pkey_get_public($pKeyP);

		if (!$pKey) return false;

		$_SS2 = "";

		foreach ($request As $key => $value)
		{
			if ($key!='_ss2')
			{
				$_SS2 .= "{$value}|";
			}
		}

		$ok = openssl_verify($_SS2, base64_decode($request['_ss2']), $pKey);

		return ($ok === 1);
	}

	function goodRequest($request)
	{
		$this->cleanRequestData($request);

		unset(
			$request['route'], $request['__utma'], $request['__utmz'],
			$request['PHPSESSID'], $request['__server'], $request['ip'],
			$request['action'], $request['id'], $request['controller']
		);

		if ($this->checkCert(null, $request)) return true;

		return $this->checkCert('public_old.key', $request);
	}

	private function cleanRequestData(&$request)
	{
		foreach ($request as $key => $value)
		{
			if (substr($key, 0, 3) == 'wp_')
			{
				unset($request[$key]);
				$request[substr($key, 3)] = $value;
			}
		}
	}
}

?>