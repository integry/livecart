<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class CCNow extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		$params['x_login'] = $this->getConfigValue('login');
		$params['x_version'] = '1.0';
		$params['x_fp_sequence'] = $this->details->invoiceID->get() . '_' . time();
		$params['x_invoice_num'] = $this->details->invoiceID->get() . '_' . time();
		$params['x_fp_arg_list'] = 'x_login^x_fp_arg_list^x_fp_sequence^x_amount^x_currency_code';

		$params['x_amount'] = $this->details->amount->get();
		$params['x_currency_code'] = $this->details->getOrder()->getCurrency()->getID();
		$params['x_method'] = 'TEST';

		// customer information
		$params['x_name'] = $this->details->getName();
		$params['x_address'] = $this->details->address->get();
		$params['x_city'] = $this->details->city->get();
		$params['x_state'] = $this->details->state->get();
		$params['x_zip'] = $this->details->postalCode->get();
		$params['x_country'] = $this->details->country->get();
		$params['x_email'] = $this->details->email->get();
		$params['x_phone'] = $this->details->phone->get();

		$params['x_fp_hash'] = $params['x_login'] . '^' . $params['x_fp_arg_list'] . '^' . $params['x_fp_sequence'] . '^' . $params['x_amount'] . '^' . $params['x_currency_code'] . '^' . $this->getConfigValue('key');
		$params['x_fp_hash'] = md5($params['x_fp_hash']);

		foreach ($this->details->getLineItems() as $index => $item)
		{
			if ('shipping' == $item['sku'])
			{
				$params['x_shipping_method'] = $item['name'];
				$params['x_shipping_amount'] = $this->roundAmount($item['price'], $params['x_currency_code']);
			}
			else
			{
				$params['x_product_sku_' . $index] = $item['sku'];
				$params['x_product_title_' . $index] = $item['name'];
				$params['x_product_quantity_' . $index] = $item['quantity'];
				$params['x_product_unitprice_' . $index] = $this->roundAmount($item['price'], $params['x_currency_code']);
				$params['x_product_url_' . $index] = $this->cancelUrl;
			}
		}

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://www.ccnow.com/cgi-local/transact.cgi?' . implode('&', $pairs);
	}

	private function roundAmount($amount, $currency)
	{
		$decimals = 'JPY' != $currency ? 2 : 0;
		$amount = round($amount, $decimals);
		return 'JPY' != $currency ? sprintf("%01.2f", $amount) : $amount;
	}

	public function notify($requestArray)
	{
		$this->saveDebug($requestArray);
		//return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return array_shift(explode('_', $requestArray['Order_Id']));
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return $this->getConfigValue('Currency');
	}

	public function isVoidable()
	{
		return false;
	}

	public function void()
	{
		return false;
	}

	private function getchecksum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey)
	{
		$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);
		return $adler;
	}

	private function verifychecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey)
	{
		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
		$adler = 1;
		$adler = $this->adler32($adler,$str);

		if($adler == $CheckSum)
			return "true" ;
		else
			return "false" ;
	}

	private function adler32($adler , $str)
	{
		$BASE =  65521 ;

		$s1 = $adler & 0xffff ;
		$s2 = ($adler >> 16) & 0xffff;
		for($i = 0 ; $i < strlen($str) ; $i++)
		{
			$s1 = ($s1 + Ord($str[$i])) % $BASE ;
			$s2 = ($s2 + $s1) % $BASE ;
				//echo "s1 : $s1 <BR> s2 : $s2 <BR>";

		}
		return $this->leftshift($s2 , 16) + $s1;
	}

	private function leftshift($str , $num)
	{
		$str = DecBin($str);

		for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
			$str = "0".$str ;

		for($i = 0 ; $i < $num ; $i++)
		{
			$str = $str."0";
			$str = substr($str , 1 ) ;
			//echo "str : $str <BR>";
		}
		return $this->cdec($str) ;
	}

	private function cdec($num)
	{
		for ($n = 0 ; $n < strlen($num) ; $n++)
		{
		   $temp = $num[$n] ;
		   $dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
		}

		return $dec;
	}
}

?>
