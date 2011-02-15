<?php

/**
 * DpdParser
 * @author Integry Systems
 */

class DpdParser
{
	public function fetch()
	{
		$vat = 0.22;
		$month = date('n');

		$surcharges = $this->fetchSurcharge();
		if(!array_key_exists($month, $surcharges))
		{
			throw new ApplicationException("Can't find surcharge for current month.");
		}
		$surcharge = $surcharges[$month];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.dpd.lv/lv/main/products/prices/prices_juridical/prices_baltic");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		if(!preg_match('/lata\s+attiec.+?bai\s+pret\s+eiro\s+\(EUR\)\,\s+(?P<LVLEUR>[0-9\,\.]+)\s+LVL\,\s+pakalpojumu/', $response, $m))
		{
			throw new ApplicationException("Can't find LVLEUR");
		}
		$LVLEUR = floatval(str_replace(',','.',$m['LVLEUR']));
		if(!preg_match_all('/<table class\=\"neotable\">(.*?)<\/table>/ism', $response, $m) || false == (count($m[0]) > 1))
		{
			throw new ApplicationException("Can't find service charge table.");
		}
		$xml = new SimpleXMLElement(str_replace('&nbsp;',' ',$m[0][1]));
		$data = array();
		$x = (1+$vat) * $surcharge;
		foreach ($xml->xpath("//tr") as $i => $row)
		{
			if  ($i == 0)
			{
				continue;
			}
			else
			{
				$weight = $this->sanitizeWeight($row->td[0]->p->strong);
				$dataItem = array(
					'weight' => $weight,
					'volume' => $this->sanitizeVolume($row->td[1]->p),
					'riga' => $this->sanitizePrice($row->td[2]->p) * $x,
					'major_city' => $this->sanitizePrice($row->td[3]->p) * $x,
					'latvia' => $this->sanitizePrice($row->td[4]->p) * $x,
					'baltic' => $this->sanitizePrice($row->td[5]->p, $LVLEUR) * $x
				);
				if (strpos($weight, '+') !== false)
				{
					$data['extra'][$weight] = $dataItem; // vai piemaksai par katriem nakošajiem x kg arī jāpierēķina klāt degvielas keficients un pvn? (tagad pierēķina)
				}
				else
				{
					$data[$weight] = $dataItem;
				}
			}
		}

		return $data;
	}

	public function fetchSurcharge()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.dpd.lv/lv/main/products/prices/prices_surcharge");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);

		if(!preg_match('/<table class\=\"neotable\">.*?<\/table>/ism', $response, $m))
		{
			throw new ApplicationException("Can't find prices surcharge.");
		}

		$xml = new SimpleXMLElement(str_replace('&nbsp;',' ',$m[0]));
		$data = array();
		foreach ($xml->xpath('//tr') as $row)
		{
			if (preg_match('/degvielas\s+koeficients\s+(?P<month>.+)/i', $row->td[0], $m))
			{
				$month = null;
				if (preg_match('/janv.+r.+/i', $m['month']))
				{
					$month = 1;
				} else if (preg_match('/febru.+r.+/i', $m['month']))
				{
					$month = 2;
				}
				else if (preg_match('/mart.+/i', $m['month']))
				{
					$month = 3;
				}
				else if (preg_match('/apr.+l.+/i', $m['month']))
				{
					$month = 4;
				}
				else if (preg_match('/maij.+/i', $m['month']))
				{
					$month = 5;
				}
				else if (preg_match('/j.+nij.+/i', $m['month']))
				{
					$month = 6;
				}
				else if (preg_match('/j.+lij.+/i', $m['month']))
				{
					$month = 7;
				}
				else if (preg_match('/august.+/i', $m['month']))
				{
					$month = 8;
				}
				else if (preg_match('/septembr.+/i', $m['month']))
				{
					$month = 9;
				}
				else if (preg_match('/oktobr.+/i', $m['month']))
				{
					$month = 10;
				}
				else if (preg_match('/novembr.+/i', $m['month']))
				{
					$month = 11;
				}
				else if (preg_match('/decembr.+/i', $m['month']))
				{
					$month = 12;
				}

				if ($month)
				{
					$data[$month] = $this->sanitizePrice($row->td[1]);
				}
			}
		}

		return $data;
	}

	private  function sanitizeWeight($value)
	{
		$value = (string)$value;
		if (preg_match("/Katri\s+n.+kamie\s*([0-9]+)\s*kg/i", $value, $m))
		{
			$value = "+".$m[1];
		}
		return $value;
	}

	private  function sanitizeVolume($value)
	{
		$value = (string)$value;
		if (preg_match('/[0-9.]/', $value))
		{
			$value = floatval($value);
		}
		else
		{
			$value = null;
		}
		return $value;
	}

	private  function sanitizePrice($value, $rate=1)
	{
		$value = (string)$value;
		if (preg_match('/[0-9.]/', $value))
		{
			$value = floatval($value) * $rate;
		}
		else
		{
			$value = null;
		}
		return $value;
	}
}

?>