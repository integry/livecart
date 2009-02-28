<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class DibsFlexWin extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		// user name
		$params['merchant'] = $this->getConfigValue('merchant');

		if ($this->getConfigValue('test'))
		{
			$params['test'] = 'yes';
		}

		// A seller reference number for a transaction
		$params['orderid'] = $this->details->invoiceID->get();
		$params['uniqueoid'] = $this->details->invoiceID->get();

		// The payment amount
		$params['amount'] = $this->details->amount->get() * 100;

		// The currency code of the payment amount.
		$params['currency'] = $this->getCurrency($this->details->currency->get());

		$params['callbackurl'] = $this->notifyUrl;
		$params['accepturl'] = $this->returnUrl;
		$params['cancelurl'] = $this->siteUrl;
		$params['lang'] = ActiveRecordModel::getApplication()->getLocaleCode();
		$params['skiplastpage'] = 1;

		$params['md5key'] = $this->getMd5Key($params);

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://payment.architrade.com/paymentweb/start.action?' . implode('&', $pairs);
	}

	private function getMd5Key($params)
	{
		$k1 = $this->getConfigValue('md51');
		$k2 = $this->getConfigValue('md52');

		if (!is_numeric($params['currency']))
		{
			$params['currency'] = $this->getCurrency($params['currency']);
		}

		return md5($k2 . md5($k1 . 'merchant=' . $params['merchant'] . '&orderid=' . $params['orderid'] . '&currency=' . $params['currency'] . '&amount=' . $params['amount']));
	}

	public function notify($requestArray)
	{
		file_put_contents(ClassLoader::getRealPath('cache.') . 'notify.php', var_export($requestArray, true));
		if ($requestArray['md5key'] == $this->getMd5Key($requestArray))
		{
			$result = new TransactionResult();
			$result->gatewayTransactionID->set($requestArray['transact']);
			$result->amount->set($requestArray['amount'] / 100);
			$result->currency->set($this->getCurrency($requestArray['currency']));
			$result->rawResponse->set($requestArray);
			$result->setTransactionType(TransactionResult::TYPE_SALE);
		}
		else
		{
			$result = new TransactionError('md5key mismatch', $requestArray);
		}

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['orderid'];
	}

	public function isHtmlResponse()
	{
		return false;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return strtoupper($currentCurrencyCode);
	}

	public function isVoidable()
	{
		return false;
	}

	public function void()
	{
		return false;
	}

	private function getCurrency($code)
	{
		$numbers = array(
					'CHE' => 947,
					'BOV' => 984,
					'BTN' => 064,
					'CLF' => 990,
					'COU' => 970,
					'EUR' => 978,
					'LSL' => 426,
					'MXV' => 979,
					'NAD' => 516,
					'UYI' => 940,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USN' => 997,
					'XOF' => 952,
					'AED' => 784,
					'AFN' => 971,
					'ALL' => 8,
					'AMD' => 51,
					'ANG' => 532,
					'AOA' => 973,
					'ARS' => 32,
					'AUD' => 36,
					'AUD' => 36,
					'AUD' => 36,
					'AUD' => 36,
					'AUD' => 36,
					'AUD' => 36,
					'AUD' => 36,
					'AUD' => 36,
					'AWG' => 533,
					'AZN' => 944,
					'BAM' => 977,
					'BBD' => 52,
					'BDT' => 50,
					'BGN' => 975,
					'BHD' => 48,
					'BIF' => 108,
					'BYR' => 974,
					'BMD' => 60,
					'BND' => 96,
					'BOB' => 68,
					'BRL' => 986,
					'BSD' => 44,
					'BWP' => 72,
					'BZD' => 84,
					'CAD' => 124,
					'CDF' => 976,
					'CHF' => 756,
					'CHF' => 756,
					'CHW' => 948,
					'CLP' => 152,
					'CNY' => 156,
					'COP' => 170,
					'CRC' => 188,
					'CUP' => 192,
					'CVE' => 132,
					'CZK' => 203,
					'DJF' => 262,
					'DKK' => 208,
					'DKK' => 208,
					'DKK' => 208,
					'DOP' => 214,
					'DZD' => 12,
					'EEK' => 233,
					'EGP' => 818,
					'ERN' => 232,
					'ETB' => 230,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'EUR' => 978,
					'FJD' => 242,
					'FKP' => 238,
					'GBP' => 826,
					'GEL' => 981,
					'GHS' => 936,
					'GIP' => 292,
					'GYD' => 328,
					'GMD' => 270,
					'GNF' => 324,
					'GTQ' => 320,
					'GWP' => 624,
					'HKD' => 344,
					'HNL' => 340,
					'HRK' => 191,
					'HTG' => 332,
					'HUF' => 348,
					'IDR' => 360,
					'ILS' => 376,
					'INR' => 356,
					'INR' => 356,
					'IQD' => 368,
					'IRR' => 364,
					'ISK' => 352,
					'YER' => 886,
					'JMD' => 388,
					'JOD' => 400,
					'JPY' => 392,
					'KES' => 404,
					'KGS' => 417,
					'KHR' => 116,
					'KYD' => 136,
					'KMF' => 174,
					'KPW' => 408,
					'KRW' => 410,
					'KWD' => 414,
					'KZT' => 398,
					'LAK' => 418,
					'LBP' => 422,
					'LYD' => 434,
					'LKR' => 144,
					'LRD' => 430,
					'LTL' => 440,
					'LVL' => 428,
					'MAD' => 504,
					'MAD' => 504,
					'MDL' => 498,
					'MGA' => 969,
					'MYR' => 458,
					'MKD' => 807,
					'MMK' => 104,
					'MNT' => 496,
					'MOP' => 446,
					'MRO' => 478,
					'MUR' => 480,
					'MVR' => 462,
					'MWK' => 454,
					'MXN' => 484,
					'MZN' => 943,
					'NGN' => 566,
					'NIO' => 558,
					'NOK' => 578,
					'NOK' => 578,
					'NOK' => 578,
					'NPR' => 524,
					'NZD' => 554,
					'NZD' => 554,
					'NZD' => 554,
					'NZD' => 554,
					'NZD' => 554,
					'OMR' => 512,
					'PAB' => 590,
					'PEN' => 604,
					'PGK' => 598,
					'PHP' => 608,
					'PYG' => 600,
					'PKR' => 586,
					'PLN' => 985,
					'QAR' => 634,
					'RON' => 946,
					'RSD' => 941,
					'RUB' => 643,
					'RWF' => 646,
					'SAR' => 682,
					'SBD' => 90,
					'SCR' => 690,
					'SDG' => 938,
					'SEK' => 752,
					'SGD' => 702,
					'SHP' => 654,
					'SYP' => 760,
					'SKK' => 703,
					'SLL' => 694,
					'SOS' => 706,
					'SRD' => 968,
					'STD' => 678,
					'SVC' => 222,
					'SZL' => 748,
					'THB' => 764,
					'TJS' => 972,
					'TMM' => 795,
					'TND' => 788,
					'TOP' => 776,
					'TRY' => 949,
					'TTD' => 780,
					'TWD' => 901,
					'TZS' => 834,
					'UAH' => 980,
					'UGX' => 800,
					'UYU' => 858,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USD' => 840,
					'USS' => 998,
					'UZS' => 860,
					'VEF' => 937,
					'VND' => 704,
					'VUV' => 548,
					'WST' => 882,
					'XAF' => 950,
					'XAF' => 950,
					'XAF' => 950,
					'XAF' => 950,
					'XAF' => 950,
					'XAF' => 950,
					'XAG' => 961,
					'XAU' => 959,
					'XBA' => 955,
					'XBB' => 956,
					'XBC' => 957,
					'XBD' => 958,
					'XCD' => 951,
					'XCD' => 951,
					'XCD' => 951,
					'XCD' => 951,
					'XCD' => 951,
					'XCD' => 951,
					'XCD' => 951,
					'XCD' => 951,
					'XDR' => 960,
					'XOF' => 952,
					'XOF' => 952,
					'XOF' => 952,
					'XOF' => 952,
					'XOF' => 952,
					'XOF' => 952,
					'XOF' => 952,
					'XPD' => 964,
					'XPF' => 953,
					'XPF' => 953,
					'XPF' => 953,
					'XPT' => 962,
					'XTS' => 963,
					'XXX' => 999,
					'ZAR' => 710,
					'ZAR' => 710,
					'ZAR' => 710,
					'ZMK' => 894,
					'ZWR' => 935);

		if (is_numeric($code))
		{
			return array_search($code, $numbers);
		}
		else
		{
			return isset($numbers[$code]) ? $numbers[$code] : 0;
		}
	}
}

?>