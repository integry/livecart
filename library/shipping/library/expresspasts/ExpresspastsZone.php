<?php

/**
 * ExpresspastsZone
 * @author Integry Systems
 */
 
class ExpresspastsZone
{
	private $zone = null;

	private $fromLocation;
	private $toCity;
	private $toZip;

	const ZONE_1 = 1;
	const ZONE_2 = 2;
	const ZONE_3 = 3;

	// value as value in configuration
	const LOCATION_1 = 'EXPRESSPASTS_FROM_LOCATION_1';
	const LOCATION_2 = 'EXPRESSPASTS_FROM_LOCATION_2';
	const LOCATION_AIZKRAUKLE = 'EXPRESSPASTS_FROM_LOCATION_AIZKRAUKLE';
	const LOCATION_ALUKSNE = 'EXPRESSPASTS_FROM_LOCATION_ALUKSNE';
	const LOCATION_BALVI = 'EXPRESSPASTS_FROM_LOCATION_BALVI';
	const LOCATION_BAUSKA = 'EXPRESSPASTS_FROM_LOCATION_BAUSKA';
	const LOCATION_CESIS = 'EXPRESSPASTS_FROM_LOCATION_CESIS';
	const LOCATION_DAUGAVPILS = 'EXPRESSPASTS_FROM_LOCATION_DAUGAVPILS';
	const LOCATION_DOBELE = 'EXPRESSPASTS_FROM_LOCATION_DOBELE';
	const LOCATION_GULBENE = 'EXPRESSPASTS_FROM_LOCATION_GULBENE';
	const LOCATION_JEKABPILS = 'EXPRESSPASTS_FROM_LOCATION_JEKABPILS';
	const LOCATION_JELGAVA = 'EXPRESSPASTS_FROM_LOCATION_JELGAVA';
	const LOCATION_KRASLAVA = 'EXPRESSPASTS_FROM_LOCATION_KRASLAVA';
	const LOCATION_KULDIGA = 'EXPRESSPASTS_FROM_LOCATION_KULDIGA';
	const LOCATION_LIEPAJA = 'EXPRESSPASTS_FROM_LOCATION_LIEPAJA';
	const LOCATION_LIMBAZI = 'EXPRESSPASTS_FROM_LOCATION_LIMBAZI';
	const LOCATION_LUDZA = 'EXPRESSPASTS_FROM_LOCATION_LUDZA';
	const LOCATION_MADONA = 'EXPRESSPASTS_FROM_LOCATION_MADONA';
	const LOCATION_OGRE = 'EXPRESSPASTS_FROM_LOCATION_OGRE';
	const LOCATION_PREILI = 'EXPRESSPASTS_FROM_LOCATION_PREILI';
	const LOCATION_REZEKNE = 'EXPRESSPASTS_FROM_LOCATION_REZEKNE';
	const LOCATION_SALDUS = 'EXPRESSPASTS_FROM_LOCATION_SALDUS';
	const LOCATION_TALSI = 'EXPRESSPASTS_FROM_LOCATION_TALSI';
	const LOCATION_TUKUMS = 'EXPRESSPASTS_FROM_LOCATION_TUKUMS';
	const LOCATION_VALKA = 'EXPRESSPASTS_FROM_LOCATION_VALKA';
	const LOCATION_VALMIERA = 'EXPRESSPASTS_FROM_LOCATION_VALMIERA';
	const LOCATION_VENTSPILS = 'EXPRESSPASTS_FROM_LOCATION_VENTSPILS';
	const LOCATION_4 = 'EXPRESSPASTS_FROM_LOCATION_4';

	public function __construct($fromLocation=null, $toCity=null, $toZip=null)
	{
		$this->fromLocation = $fromLocation;
		$this->toCity = $this->sanitizeCity($toCity);
		$this->toZip = $this->sanitizeZip('ddd'.$toZip);
	}

	public function getZoneName()
	{
		if ($this->zone== null)
		{
			switch($this->fromLocation)
			{
				case self::LOCATION_1: // No Rīgas
					if (in_array($this->toCity, array(
						'riga', 'babite', 'balozi', 'jaunmmrupe', 'katlakalns', 'marupe',
						'pinki', 'titurga', 'ulbroka'
					))) {
						$this->zone=self::ZONE_1;
					}
					else if(in_array($this->toCity, array(
						'aizkraukle', 'aluksne', 'balvi', 'bauska', 'cesis', 'daugavpils',
						'dobele', 'gulbene', 'jekabpils', 'jelgava', 'kraslava', 'kuldiga',
						'liepaja', 'limbazi', 'ludza', 'madona', 'ogre', 'preili',
						'rezekne', 'saldus', 'talsi', 'tukums', 'valka', 'valmiera',
						'ventspils', 'bulduri', 'kauguri', 'kemeri', 'majori', 'melluzi',
						'sloka'
					))) {
						$this->zone=self::ZONE_2;
					}
					else
					{
						$this->zone=self::ZONE_3;
					}
					break;

				case self::LOCATION_2:
					if (in_array($this->toCity, array(
						'riga', 'aizkraukle','aluksne','balvi','bauska','cesis',
						'daugavpils','dobele','gulbene','jekabpils','jelgava','kraslava',
						'kuldiga','liepaja','limbazi','ludza','madona','ogre',
						'preili', 'rezekne','saldus','talsi','tukums','valka',
						'valmiera','ventspils', 'acone', 'adazi', 'allazi', 'babite',
						'baldone', 'balozi', 'bulduri', 'carnikava', 'cekule', 'daugmale',
						'garkalne', 'gauja', 'inciems', 'incukalns', 'jaunmarupe', 'jaunolaine',
						'jaunskulte', 'judazi', 'kadaga', 'katlakalns', 'kauguri', 'kekava',
						'kemeri', 'majori', 'malpils', 'marupe', 'melluzi', 'mezinieki'
					))) {
						$this->zone=self::ZONE_2;
					}
					else if ($this->toZip >= 2101 && $this->toZip <= 2170)
					{
						$this->zone=self::ZONE_2;
					}
					else
					{
						$this->zone=self::ZONE_3;
					}
					break;
				case self::LOCATION_AIZKRAUKLE:
				case self::LOCATION_ALUKSNE:
				case self::LOCATION_BALVI:
				case self::LOCATION_BAUSKA:
				case self::LOCATION_CESIS:
				case self::LOCATION_DAUGAVPILS:
				case self::LOCATION_DOBELE:
				case self::LOCATION_GULBENE:
				case self::LOCATION_JEKABPILS:
				case self::LOCATION_JELGAVA:
				case self::LOCATION_KRASLAVA:
				case self::LOCATION_KULDIGA:
				case self::LOCATION_LIEPAJA:
				case self::LOCATION_LIMBAZI:
				case self::LOCATION_LUDZA:
				case self::LOCATION_MADONA:
				case self::LOCATION_OGRE:
				case self::LOCATION_PREILI:
				case self::LOCATION_REZEKNE:
				case self::LOCATION_SALDUS:
				case self::LOCATION_TALSI:
				case self::LOCATION_TUKUMS:
				case self::LOCATION_VALKA:
				case self::LOCATION_VALMIERA:
				case self::LOCATION_VENTSPILS:
					if ('EXPRESSPASTS_FROM_LOCATION_'.strtoupper($this->toCity) == $this->fromLocation)
					{
						$this->zone=self::ZONE_1;
					} else if(in_array($this->toCity, array(
						'riga', 'aizkraukle', 'aluksne', 'balvi', 'bauska', 'cesis',
						'daugavpils', 'dobele', 'gulbene', 'jekabpils', 'jelgava', 'kraslava',
						'kuldiga', 'liepaja', 'limbazi', 'ludza', 'madona', 'ogre',
						'preili', 'rezekne', 'saldus', 'talsi', 'tukums', 'valka',
						'valmiera', 'ventspils'
					))) {
						$this->zone = self::ZONE_2;
					}
					else if ($this->toZip >= 2101 && $this->toZip <= 2170)
					{
						$this->zone=self::ZONE_2;
					}
					else
					{
						$this->zone=self::ZONE_3;
					}
					break;
				case self::LOCATION_4:
					$this->zone=self::ZONE_3;
					break;
			}
		}

		return $this->zone;
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
		if (preg_match('/(?P<numberic_zip>\d+)/', $value, $m))
		{
			return $m['numberic_zip'];
		}
		return null;
	}
}
?>