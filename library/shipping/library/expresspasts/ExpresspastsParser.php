<?php

/**
 * ExpresspastsParser
 * @author Integry Systems
 */
 
class ExpresspastsParser
{

	public function fetch()
	{
		$urls = array();
		$config = ActiveRecordModel::getApplication()->getConfig();
		$hasContract = (bool)$config->get('EXPRESSPASTS_CONTRACTUAL_CLIENT');
		$serviceType = $config->get('EXPRESSPASTS_SERVICE_TYPE');

		if (isset($serviceType['EXPRESSPASTS_ECONOMIC']) && $serviceType['EXPRESSPASTS_ECONOMIC'])
		{
			if ($hasContract)
			{
				$urls['contract_economic'] = 'http://www.expresspasts.lv/lv/pakalpojumi/latvija/ekonomisks/cenas_liguma_klientiem.html';
			} else {
				$urls['economic'] = 'http://www.expresspasts.lv/lv/pakalpojumi/latvija/ekonomisks/cenas.html';
			}
		}

		if (isset($serviceType['EXPRESSPASTS_STANDART']) && $serviceType['EXPRESSPASTS_STANDART'])
		{
			if ($hasContract)
			{
				$urls['contract_standart'] = 'http://www.expresspasts.lv/lv/pakalpojumi/latvija/standarts/cenas_liguma_klientiem.html';
			} else {
				$urls['standart'] = 'http://www.expresspasts.lv/lv/pakalpojumi/latvija/standarts/cenas.html';
			}
		}

		if (0 == count($urls))
		{
			return array();
		}

		foreach($urls as $type=>$url)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$response = curl_exec($ch);
			curl_close($ch);
			if(!preg_match('/<table.*\b[^>]*>(.*?)<\/table>/i', $response,$m))
			{
				throw new ApplicationException("Can't find service charge table,expresspasts.lv has been changed or can't connect.");
			}

			$xml = new SimpleXMLElement($m[0]);
			$data[$type] = array();
			foreach($xml->xpath('//tr') as $nr=>$row)
			{
				if ($nr == 0)
				{
					// header

					continue;
				}
				else if($nr == 1)
				{
					// zone neames
					if (trim($row->td[1]) != '1.zona' && trim($row->td[2]) != '2.zona' && trim($row->td[3]) != '3.zona')
					{
						throw new ApplicationException("Can't parse service charge table, probably HTML has been changed.");
					}
					continue;
				}

				$data[$type][$this->sanitizeWeight($row->td[0])] = array(
					'zone1'=>$this->sanitizeCurrency($row->td[1]),
					'zone2'=>$this->sanitizeCurrency($row->td[2]),
					'zone3'=>$this->sanitizeCurrency($row->td[3])
				);
			}
			ksort($data[$type]);
		}
		return $data;
	}

	private function sanitizeWeight($value)
	{
		$s = (string)$value;
		$weight = 0;
		if (preg_match('/l.*dz\s*(?P<weight>[0-9.]+)\s*(?P<unit>kg|g)/', strtolower((string)$value), $m))
		{
			$weight = floatval($m['weight']);
			if ($m['unit'] == 'g')
			{
				$weight /= 1000;
			}
			// $weight = '<'.$weight;
		}
		return (string)$weight;
	}

	private function sanitizeCurrency($value)
	{
		$value = trim((string)$value);
		if (preg_match('/[0-9\.]+/', $value))
		{
			return floatval($value);
		}
		return null;
	}
}

?>