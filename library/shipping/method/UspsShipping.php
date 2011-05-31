<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');

/**
 *
 * @package library.shipping.method
 * @author Integry Systems
 */
class UspsShipping extends ShippingRateCalculator
{
	private $service;

	private $container;

	public function getProviderName()
	{
		return 'USPS';
	}

	public function getAllRates()
	{
		$return = new ShippingRateSet();

		if ('US' == $this->destCountry)
		{
			$services = array_keys((array)$this->getConfigValue('domestic'));
			foreach ($services as $service)
			{
				$this->setService($service);
				$rates = $this->getRates();

				if ($rates instanceof ShippingRateSet)
				{
					$return->merge($rates);
				}
			}
		}
		else
		{
			$rates = $this->getRates();
			$services = array_keys((array)$this->getConfigValue('international'));

			if ($rates instanceof ShippingRateSet)
			{
				foreach ($rates as $rate)
				{
					$name = $rate->getServiceName();
					foreach ($services as $service)
					{
						if (substr($name, 0, strlen($service) + 2) == $service . ' (')
						{
							$return->add($rate);
							break;
						}
					}
				}
			}
		}

		return $return;
	}

	public function getRates()
	{
		include_once(dirname(__file__) . '/../library/usps/usps.php');

		// Priority mail only supports flat-rate or unspecified containers
		if (('Priority' == $this->service) && strpos(strtolower($this->container), 'flat') === false)
		{
			$this->container = '';
		}

		$usps = new USPSHandler();

		$usps->setServer($this->getConfigValue('server', 'http://production.shippingapis.com/ShippingAPI.dll'));
		$usps->setUserName($this->getConfigValue('userId'));

		$usps->setOrigZip($this->sourceZip);
		$usps->setDestZip($this->destZip);

		$country = isset($this->countries[$this->destCountry]) ? $this->countries[$this->destCountry] : 'USA';
		$usps->setCountry($country);

		// get weight in pounds/ounces
		$weight = $this->weight * 1000;
		$pounds = floor($weight / 453.59237);
		$ounces = ceil(($weight % 453.59237) / 28.3495231);
		$usps->setWeight($pounds, $ounces);

		$usps->setMachinable($this->getConfigValue('isMachinable') ? 'TRUE' : 'FALSE');
		$usps->setService($this->service);
		$usps->setSize($this->getConfigValue('size', 'Regular'));

		if (!empty($this->container))
		{
			$usps->setContainer($this->container);
		}

		$price = $usps->getPrice();

		// success
		if (isset($price->list))
		{
			$result = new ShippingRateSet();
			foreach ($price->list as $rate)
			{
				$r = new ShippingRateResult();

				$type = $rate->svcdescription;
				$type = str_replace(array('&lt', ';sup', '&gt;', '&amp;', 'amp;', 'reg;', ';/sup', 'trade;'), '', $type);
				$type = str_replace('**', '', $type);

				$r->setServiceName(isset($rate->mailservice) ? $rate->mailservice : $type . ' ('. $rate->svccommitments .')');
				$r->setCost($rate->rate, 'USD');
				$r->setClassName(get_class($this));
				$r->setProviderName($this->getProviderName());
				$result->add($r);
			}
		}
		// error
		else
		{
			$errorMsg = isset($price->error) ? $price->error->description : '';
			$result = new ShippingRateError($errorMsg);
		}

		$result->setRawResponse($price);

		return $result;
	}

	public function setMachinable($isMachinable = true)
	{
		$this->setConfigValue('isMachinable', $isMachinable);
	}

	public function setService($service)
	{
		$this->service = $service;

		if ('Express' == $service)
		{
			$this->setContainer('Flat Rate Envelope');
		}
		else if ('Priority' == $service)
		{
			$size = $this->getConfigValue('size');
			$package = $this->getConfigValue('priorityPackageType');

			$this->setContainer($package);
		}
	}

	public function setUserId($userId)
	{
		$this->setConfigValue('userId', $userId);
	}

	public function setContainer($container)
	{
		$this->container = $container;

		if (!$container)
		{
			$this->container = null;
		}
	}

	public function setSize($size)
	{
		$this->size = $size;
	}

	private $countries = array(

		'AD' => 'Andorra',
		'AE' => 'United Arab Emirates',
		'AF' => 'Afghanistan',
		'AG' => 'Antigua and Barbuda',
		'AI' => 'Anguilla',
		'AL' => 'Albania',
		'AM' => 'Armenia',
		'AN' => 'Netherlands Antilles',
		'AO' => 'Angola',
		'AQ' => 'Antarctica',
		'AR' => 'Argentina',
		'AS' => 'American Samoa',
		'AT' => 'Austria',
		'AU' => 'Australia',
		'AW' => 'Aruba',
		'AX' => 'Aland Islands',
		'AZ' => 'Azerbaijan',
		'BA' => 'Bosnia and Herzegovina',
		'BB' => 'Barbados',
		'BD' => 'Bangladesh',
		'BE' => 'Belgium',
		'BF' => 'Burkina Faso',
		'BG' => 'Bulgaria',
		'BH' => 'Bahrain',
		'BI' => 'Burundi',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BN' => 'Brunei',
		'BO' => 'Bolivia',
		'BQ' => 'British Antarctic Territory',
		'BR' => 'Brazil',
		'BS' => 'Bahamas',
		'BT' => 'Bhutan',
		'BV' => 'Bouvet Island',
		'BW' => 'Botswana',
		'BY' => 'Belarus',
		'BZ' => 'Belize',
		'CA' => 'Canada',
		'CC' => 'Cocos (Keeling) Islands',
		'CD' => 'Congo (Kinshasa)',
		'CF' => 'Central African Republic',
		'CG' => 'Congo (Brazzaville)',
		'CH' => 'Switzerland',
		'CI' => 'Ivory Coast',
		'CK' => 'Cook Islands',
		'CL' => 'Chile',
		'CM' => 'Cameroon',
		'CN' => 'China',
		'CO' => 'Colombia',
		'CR' => 'Costa Rica',
		'CS' => 'Serbia And Montenegro',
		'CT' => 'Canton and Enderbury Islands',
		'CU' => 'Cuba',
		'CV' => 'Cape Verde',
		'CX' => 'Christmas Island',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DD' => 'East Germany',
		'DE' => 'Germany',
		'DJ' => 'Djibouti',
		'DK' => 'Denmark',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'DZ' => 'Algeria',
		'EC' => 'Ecuador',
		'EE' => 'Estonia',
		'EG' => 'Egypt',
		'EH' => 'Western Sahara',
		'ER' => 'Eritrea',
		'ES' => 'Spain',
		'ET' => 'Ethiopia',
		'FI' => 'Finland',
		'FJ' => 'Fiji',
		'FK' => 'Falkland Islands',
		'FM' => 'Micronesia',
		'FO' => 'Faroe Islands',
		'FQ' => 'French Southern and Antarctic Territories',
		'FR' => 'France',
		'FX' => 'Metropolitan France',
		'GA' => 'Gabon',
		'GB' => 'Great Britain and Northern Ireland',
		'GD' => 'Grenada',
		'GE' => 'Georgia',
		'GF' => 'French Guiana',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GL' => 'Greenland',
		'GM' => 'Gambia',
		'GN' => 'Guinea',
		'GP' => 'Guadeloupe',
		'GQ' => 'Equatorial Guinea',
		'GR' => 'Greece',
		'GS' => 'South Georgia and the South Sandwich Islands',
		'GT' => 'Guatemala',
		'GU' => 'Guam',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HK' => 'Hong Kong S.A.R., China',
		'HM' => 'Heard Island and McDonald Islands',
		'HN' => 'Honduras',
		'HR' => 'Croatia',
		'HT' => 'Haiti',
		'HU' => 'Hungary',
		'ID' => 'Indonesia',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IN' => 'India',
		'IO' => 'British Indian Ocean Territory',
		'IQ' => 'Iraq',
		'IR' => 'Iran',
		'IS' => 'Iceland',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JO' => 'Jordan',
		'JP' => 'Japan',
		'JT' => 'Johnston Island',
		'KE' => 'Kenya',
		'KG' => 'Kyrgyzstan',
		'KH' => 'Cambodia',
		'KI' => 'Kiribati',
		'KM' => 'Comoros',
		'KN' => 'Saint Kitts and Nevis',
		'KP' => 'North Korea',
		'KR' => 'South Korea',
		'KW' => 'Kuwait',
		'KY' => 'Cayman Islands',
		'KZ' => 'Kazakhstan',
		'LA' => 'Laos',
		'LB' => 'Lebanon',
		'LC' => 'Saint Lucia',
		'LI' => 'Liechtenstein',
		'LK' => 'Sri Lanka',
		'LR' => 'Liberia',
		'LS' => 'Lesotho',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'LV' => 'Latvia',
		'LY' => 'Libya',
		'MA' => 'Morocco',
		'MC' => 'Monaco',
		'MD' => 'Moldova',
		'MG' => 'Madagascar',
		'MH' => 'Marshall Islands',
		'MI' => 'Midway Islands',
		'MK' => 'Macedonia',
		'ML' => 'Mali',
		'MM' => 'Myanmar',
		'MN' => 'Mongolia',
		'MO' => 'Macao S.A.R., China',
		'MP' => 'Northern Mariana Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MS' => 'Montserrat',
		'MT' => 'Malta',
		'MU' => 'Mauritius',
		'MV' => 'Maldives',
		'MW' => 'Malawi',
		'MX' => 'Mexico',
		'MY' => 'Malaysia',
		'MZ' => 'Mozambique',
		'NA' => 'Namibia',
		'NC' => 'New Caledonia',
		'NE' => 'Niger',
		'NF' => 'Norfolk Island',
		'NG' => 'Nigeria',
		'NI' => 'Nicaragua',
		'NL' => 'Netherlands',
		'NO' => 'Norway',
		'NP' => 'Nepal',
		'NQ' => 'Dronning Maud Land',
		'NR' => 'Nauru',
		'NT' => 'Neutral Zone',
		'NU' => 'Niue',
		'NZ' => 'New Zealand',
		'OM' => 'Oman',
		'PA' => 'Panama',
		'PC' => 'Pacific Islands Trust Territory',
		'PE' => 'Peru',
		'PF' => 'French Polynesia',
		'PG' => 'Papua New Guinea',
		'PH' => 'Philippines',
		'PK' => 'Pakistan',
		'PL' => 'Poland',
		'PM' => 'Saint Pierre and Miquelon',
		'PN' => 'Pitcairn',
		'PR' => 'Puerto Rico',
		'PS' => 'Palestinian Territory',
		'PT' => 'Portugal',
		'PU' => 'U.S. Miscellaneous Pacific Islands',
		'PW' => 'Palau',
		'PY' => 'Paraguay',
		'PZ' => 'Panama Canal Zone',
		'QA' => 'Qatar',
		'QO' => 'Outlying Oceania',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'SA' => 'Saudi Arabia',
		'SB' => 'Solomon Islands',
		'SC' => 'Seychelles',
		'SD' => 'Sudan',
		'SE' => 'Sweden',
		'SG' => 'Singapore',
		'SH' => 'Saint Helena',
		'SI' => 'Slovenia',
		'SJ' => 'Svalbard and Jan Mayen',
		'SK' => 'Slovakia',
		'SL' => 'Sierra Leone',
		'SM' => 'San Marino',
		'SN' => 'Senegal',
		'SO' => 'Somalia',
		'SR' => 'Suriname',
		'ST' => 'Sao Tome and Principe',
		'SV' => 'El Salvador',
		'SY' => 'Syria',
		'SZ' => 'Swaziland',
		'TC' => 'Turks and Caicos Islands',
		'TD' => 'Chad',
		'TF' => 'French Southern Territories',
		'TG' => 'Togo',
		'TH' => 'Thailand',
		'TJ' => 'Tajikistan',
		'TK' => 'Tokelau',
		'TL' => 'East Timor',
		'TM' => 'Turkmenistan',
		'TN' => 'Tunisia',
		'TO' => 'Tonga',
		'TR' => 'Turkey',
		'TT' => 'Trinidad and Tobago',
		'TV' => 'Tuvalu',
		'TW' => 'Taiwan',
		'TZ' => 'Tanzania',
		'UA' => 'Ukraine',
		'UG' => 'Uganda',
		'UM' => 'United States Minor Outlying Islands',
		'US' => 'USA',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VA' => 'Vatican',
		'VC' => 'Saint Vincent and the Grenadines',
		'VD' => 'North Vietnam',
		'VE' => 'Venezuela',
		'VG' => 'British Virgin Islands',
		'VI' => 'U.S. Virgin Islands',
		'VN' => 'Vietnam',
		'VU' => 'Vanuatu',
		'WF' => 'Wallis and Futuna',
		'WK' => 'Wake Island',
		'WS' => 'Samoa',
		'YD' => 'People\'s Democratic Republic of Yemen',
		'YE' => 'Yemen',
		'YT' => 'Mayotte',
		'ZA' => 'South Africa',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

}

?>