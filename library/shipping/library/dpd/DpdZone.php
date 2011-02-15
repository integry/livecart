<?php

/**
 * DpdZone
 * @author Integry Systems
 */
 
class DpdZone
{

	private $toCity;
	private $toZip;
	private $toCountry;
	public function __construct($toCity=null, $toZip=null, $toCountry = null)
	{
		$this->toCity = $this->sanitizeCity($toCity);
		$this->toZip = $this->sanitizeZip($toZip);
		$this->toCountry = $toCountry;
	}

	public function getZoneName()
	{
		if ($this->toCity == 'riga')
		{
			return 'riga';
		}
		else if(
			in_array($this->toCity,
				array(
					'aizkraukle', 'aluksne', 'balvi', 'bauska', 'cesis', 'daugavpils',
					'dobele', 'gulbene', 'jelgava', 'jekabpils', 'jurmala', 'kraslava',
					'kuldiga', 'liepaja', 'limbazi', 'ludza', 'madona', 'ogre',
					'preili', 'rezekne', 'saldus', 'talsi', 'tukums', 'valka',
					'valmiera', 'ventspils'
				)
			)
				||
			in_array($this->toZip,
				array(
					// Ādažu novads
					'LV-2103','LV-2137','LV-2163','LV-2164','LV-2136',
					// Babītes novads
					'LV-2101','LV-2107','LV-2011','LV-2105',
					// Baldones novads
					'LV-2125',
					// Carnikavas novads

					// Garkalnes novads
					'LV-1023','LV-1024','LV-1064',
					// Inčukalna novads
					'LV-2140','LV-2141','LV-2154',
					// Krimuldas novads
					'LV-2142','LV-2144','LV-2145','LV-2150','LV-4012',
					// Mālpils novads
					'LV-2152',
					// Mārupes novads
					'LV-1053','LV-2108','LV-2166','LV-2167','LV-1044','LV-1058',
					// Olaines novads
					'LV-2114','LV-2113','LV-2127',
					// Ropažu novads
					'LV-2118','LV-2133','LV-2135',
					// Salaspils novads
					'LV-2169','LV-2121','LV-2117','LV-2119','LV-5015',
					// Saulkrastu novads
					'LV-2160','LV-2161',
					// Siguldas novads
					'LV-2170','LV-2151',
					// Stopiņu novads
					'LV-2130','LV-1057',
					// Sējas novads
					'LV-2162',
					// Ķekavas novads
					'LV-2112','LV-2128','LV-2124','LV-1076','LV-2111','LV-2123'
				)
			)
		)
		{
			return 'major_city';
		}
		else if($this->toCountry == 'LV')
		{
			return 'latvia';
		}
		else if($this->toCountry == 'LT' || $this->toCountry == 'EE')
		{
			return 'baltic';
		}
		return false;
		//throw new Exception("Can't detect zone. DPD may not ship to given address");
	}

	public function __toString()
	{
		return (string)$this->getZoneName();
	}

	private function sanitizeCity($value)
	{
		// pseido iconv
		$value = str_replace(
			array('ē','ŗ','ū','ī','ō','ā','š','ģ','ķ','ļ','ž','č','ņ','Ē','Ŗ','Ū','Ī','Ō','Ā','Š','Ķ','Ļ','Ž','Č','Ņ', "\n", "\t"),
			array('e','r','u','i','o','a','s','g','k','l','z','c','n','E','R','U','I','O','A','S','K','L','Z','C','N', '',''),
			$value
		);
		$value = trim($value);
		return strtolower($value);
	}

	private function sanitizeZip($value)
	{
		$value = strtoupper(trim((string)$value));
		if (preg_match('/^(LV\-|LV)*\d+$/i', $value, $m))
		{
			return $value;
		}
		return null;
	}
}

?>