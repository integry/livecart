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
		$countries = array(
			'AF' => 'AFG',
			'AL' => 'ALB',
			'DZ' => 'DZA',
			'AS' => 'ASM',
			'AD' => 'AND',
			'AO' => 'AGO',
			'AI' => 'AIA',
			'AQ' => 'ATA',
			'AG' => 'ATG',
			'AR' => 'ARG',
			'AM' => 'ARM',
			'AW' => 'ABW',
			'AU' => 'AUS',
			'AT' => 'AUT',
			'AZ' => 'AZE',
			'BS' => 'BHS',
			'BH' => 'BHR',
			'BD' => 'BGD',
			'BB' => 'BRB',
			'BY' => 'BLR',
			'BE' => 'BEL',
			'BZ' => 'BLZ',
			'BJ' => 'BEN',
			'BM' => 'BMU',
			'BT' => 'BTN',
			'BO' => 'BOL',
			'BA' => 'BIH',
			'BW' => 'BWA',
			'BV' => 'BVT',
			'BR' => 'BRA',
			'IO' => 'IOT',
			'BN' => 'BRN',
			'BG' => 'BGR',
			'BF' => 'BFA',
			'BI' => 'BDI',
			'KH' => 'KHM',
			'CM' => 'CMR',
			'CA' => 'CAN',
			'CV' => 'CPV',
			'KY' => 'CYM',
			'CF' => 'CAF',
			'TD' => 'TCD',
			'CL' => 'CHL',
			'CN' => 'CHN',
			'CX' => 'CXR',
			'CC' => 'CCK',
			'CO' => 'COL',
			'KM' => 'COM',
			'CG' => 'COG',
			'CD' => 'COD',
			'CK' => 'COK',
			'CR' => 'CRI',
			'CI' => 'CIV',
			'HR' => 'HRV',
			'CU' => 'CUB',
			'CY' => 'CYP',
			'CZ' => 'CZE',
			'DK' => 'DNK',
			'DJ' => 'DJI',
			'DM' => 'DMA',
			'DO' => 'DOM',
			'TP' => 'TMP',
			'EC' => 'ECU',
			'EG' => 'EGY',
			'SV' => 'SLV',
			'GQ' => 'GNQ',
			'ER' => 'ERI',
			'EE' => 'EST',
			'ET' => 'ETH',
			'FK' => 'FLK',
			'FO' => 'FRO',
			'FJ' => 'FJI',
			'FI' => 'FIN',
			'FR' => 'FRA',
			'FX' => 'FXX',
			'GF' => 'GUF',
			'PF' => 'PYF',
			'TF' => 'ATF',
			'GA' => 'GAB',
			'GM' => 'GMB',
			'GE' => 'GEO',
			'DE' => 'DEU',
			'GH' => 'GHA',
			'GI' => 'GIB',
			'GR' => 'GRC',
			'GL' => 'GRL',
			'GD' => 'GRD',
			'GP' => 'GLP',
			'GU' => 'GUM',
			'GT' => 'GTM',
			'GN' => 'GIN',
			'GW' => 'GNB',
			'GY' => 'GUY',
			'HT' => 'HTI',
			'HM' => 'HMD',
			'VA' => 'VAT',
			'HN' => 'HND',
			'HK' => 'HKG',
			'HU' => 'HUN',
			'IS' => 'ISL',
			'IN' => 'IND',
			'ID' => 'IDN',
			'IR' => 'IRN',
			'IQ' => 'IRQ',
			'IE' => 'IRL',
			'IL' => 'ISR',
			'IT' => 'ITA',
			'JM' => 'JAM',
			'JP' => 'JPN',
			'JO' => 'JOR',
			'KZ' => 'KAZ',
			'KE' => 'KEN',
			'KI' => 'KIR',
			'KP' => 'PRK',
			'KR' => 'KOR',
			'KW' => 'KWT',
			'KG' => 'KGZ',
			'LA' => 'LAO',
			'LV' => 'LVA',
			'LB' => 'LBN',
			'LS' => 'LSO',
			'LR' => 'LBR',
			'LY' => 'LBY',
			'LI' => 'LIE',
			'LT' => 'LTU',
			'LU' => 'LUX',
			'MO' => 'MAC',
			'MK' => 'MKD',
			'MG' => 'MDG',
			'MW' => 'MWI',
			'MY' => 'MYS',
			'MV' => 'MDV',
			'ML' => 'MLI',
			'MT' => 'MLT',
			'MH' => 'MHL',
			'MQ' => 'MTQ',
			'MR' => 'MRT',
			'MU' => 'MUS',
			'YT' => 'MYT',
			'MX' => 'MEX',
			'FM' => 'FSM',
			'MD' => 'MDA',
			'MC' => 'MCO',
			'MN' => 'MNG',
			'MS' => 'MSR',
			'MA' => 'MAR',
			'MZ' => 'MOZ',
			'MM' => 'MMR',
			'NA' => 'NAM',
			'NR' => 'NRU',
			'NP' => 'NPL',
			'NL' => 'NLD',
			'AN' => 'ANT',
			'NC' => 'NCL',
			'NZ' => 'NZL',
			'NI' => 'NIC',
			'NE' => 'NER',
			'NG' => 'NGA',
			'NU' => 'NIU',
			'NF' => 'NFK',
			'MP' => 'MNP',
			'NO' => 'NOR',
			'OM' => 'OMN',
			'PK' => 'PAK',
			'PW' => 'PLW',
			'PA' => 'PAN',
			'PG' => 'PNG',
			'PY' => 'PRY',
			'PE' => 'PER',
			'PH' => 'PHL',
			'PN' => 'PCN',
			'PL' => 'POL',
			'PT' => 'PRT',
			'PR' => 'PRI',
			'QA' => 'QAT',
			'RE' => 'REU',
			'RO' => 'ROM',
			'RU' => 'RUS',
			'RW' => 'RWA',
			'KN' => 'KNA',
			'LC' => 'LCA',
			'VC' => 'VCT',
			'WS' => 'WSM',
			'SM' => 'SMR',
			'ST' => 'STP',
			'SA' => 'SAU',
			'SN' => 'SEN',
			'SC' => 'SYC',
			'SL' => 'SLE',
			'SG' => 'SGP',
			'SK' => 'SVK',
			'SI' => 'SVN',
			'SB' => 'SLB',
			'SO' => 'SOM',
			'ZA' => 'ZAF',
			'GS' => 'SGS',
			'ES' => 'ESP',
			'LK' => 'LKA',
			'SH' => 'SHN',
			'PM' => 'SPM',
			'SD' => 'SDN',
			'SR' => 'SUR',
			'SJ' => 'SJM',
			'SZ' => 'SWZ',
			'SE' => 'SWE',
			'CH' => 'CHE',
			'SY' => 'SYR',
			'TW' => 'TWN',
			'TJ' => 'TJK',
			'TZ' => 'TZA',
			'TH' => 'THA',
			'TG' => 'TGO',
			'TK' => 'TKL',
			'TO' => 'TON',
			'TT' => 'TTO',
			'TN' => 'TUN',
			'TR' => 'TUR',
			'TM' => 'TKM',
			'TC' => 'TCA',
			'TV' => 'TUV',
			'UG' => 'UGA',
			'UA' => 'UKR',
			'AE' => 'ARE',
			'GB' => 'GBR',
			'US' => 'USA',
			'UM' => 'UMI',
			'UY' => 'URY',
			'UZ' => 'UZB',
			'VU' => 'VUT',
			'VE' => 'VEN',
			'VN' => 'VNM',
			'VG' => 'VGB',
			'VI' => 'VIR',
			'WF' => 'WLF',
			'EH' => 'ESH',
			'YE' => 'YEM',
			'YU' => 'YUG',
			'ZM' => 'ZMB',
			'ZW' => 'ZWE'
		);

		if (isset($countries[$twoLetterCode]))
		{
			return $countries[$twoLetterCode];
		}
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