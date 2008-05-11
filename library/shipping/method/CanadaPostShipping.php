<?php

include_once(dirname(__file__) . '/../ShippingRateCalculator.php');

/**
 *
 * @package library.shipping.method
 * @author Integry Systems
 */
class CanadaPostShipping extends ShippingRateCalculator
{
	private $service;

	private $container;

	public function getProviderName()
	{
		return 'Canada Post';
	}

	public function getAllRates()
	{
		$return = new ShippingRateSet();

		$country = isset($this->countries[$this->destCountry]) ? $this->countries[$this->destCountry] : $this->countries['CA'];

		$xml = '<?xml version="1.0" ?>
		<eparcel>
			<language>en</language>
			<ratesAndServicesRequest>
				<merchantCPCID> ' . $this->getConfigValue('merchantID') . ' </merchantCPCID>
				<fromPostalCode> ' . htmlspecialchars($this->sourceZip) . ' </fromPostalCode>
				<turnAroundTime>24</turnAroundTime>
				<lineItems>
					<item>
						<quantity> 1 </quantity>
						<weight> ' . round($this->weight / 1000, 3) . ' </weight>
						<length> 1 </length>
						<width> 1 </width>
						<height> 1 </height>
						<description> LiveCart shipment </description>
						<readyToShip />
					</item>
				</lineItems>
				<provOrState> ' . htmlspecialchars($this->destState) . ' </provOrState>
				<country> ' . htmlspecialchars($this->destCountry) . ' </country>
				<postalCode> ' . htmlspecialchars($this->destZip) . ' </postalCode>
			</ratesAndServicesRequest>
		</eparcel>';

		$result = $this->httpPost('sellonline.canadapost.ca', '/', 'XMLRequest=' . urlencode($xml));

		$xml = simplexml_load_string($result);
		if ($xml)
		{
			if (isset($xml->ratesAndServicesResponse->product))
			{
				foreach ($xml->ratesAndServicesResponse->product as $rate)
				{
					$r = new ShippingRateResult();
					$r->setServiceName($rate->name . ' ('. $rate->deliveryDate .')');
					$r->setCost((string)$rate->rate, 'CAD');
					$r->setClassName(get_class($this));
					$r->setProviderName($this->getProviderName());
					$r->setRawResponse($result);
					$return->add($r);
				}
			}
			else
			{
				$return = new ShippingRateError(isset($xml->error->statusMessage) ? $xml->error->statusMessage : '');
			}
		}

		$return->setRawResponse($result);

		return $return;
	}

	public function getRates()
	{
		$rates = $this->getAllRates();

		if ($rates instanceof ShippingRateSet)
		{
			$result = new ShippingRateSet();
			$result->setRawResponse($rates->getRawResponse());
			foreach ($rates as $rate)
			{
				if (substr($rate->getServiceName(), 0, strlen($this->service)) == $this->service)
				{
					$result->add($rate);
				}
			}
		}
		else
		{
			$result = $rates;
		}

		return $result;
	}

	private function httpPost($host, $uri, $postdata, $port = 30000)
	{
		$da = fsockopen($host, $port, $errno, $errstr);
		if (!$da)
		{
			// return null;
			echo "$errstr ($errno)<br/>\n";
			echo $da;
		}
		else
		{
			$salida ="POST $uri  HTTP/1.1\r\n";
			$salida.="Host: $host\r\n";
			$salida.="User-Agent: PHP Script\r\n";
			$salida.="Content-Type: application/x-www-form-urlencoded\r\n";
			$salida.="Content-Length: ".strlen($postdata)."\r\n";
			$salida.="Connection: close\r\n\r\n";
			$salida.=$postdata;

			fwrite($da, $salida);

			$response = '';
			while (!feof($da))
			{
				$response .= fgets($da);
			}

			$response = str_replace("\r", '', $response);

			$response=split("\n\n", $response, 2);
			$header=$response[0];
			$responsecontent=$response[1];
			if(!(strpos($header,"Transfer-Encoding: chunked")===false)){
				$aux=split("\r\n",$responsecontent);
				for($i=0;$i<count($aux);$i++)
					if($i==0 || ($i%2==0))
						$aux[$i]="";
				$responsecontent=implode("",$aux);
			}
			return chop($responsecontent);
		}
	}

	public function setService($service)
	{
		$this->service = $service;
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
		'GB' => 'United Kingdom',
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