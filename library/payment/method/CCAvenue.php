<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class CCAvenue extends ExternalPayment
{
	public function getUrl()
	{
		$params = array();

		$params['Merchant_Id'] = $this->getConfigValue('Merchant_Id');
		$params['Order_Id'] = $this->details->invoiceID->get() . '_' . rand(1, 1000000);
		$params['Amount'] = $this->details->amount->get();
		$params['Redirect_Url'] = $this->notifyUrl;
		$params['Checksum'] = $this->getCheckSum($params['Merchant_Id'],$params['Amount'],$params['Order_Id'] ,$params['Redirect_Url'], $this->getConfigValue('WorkingKey'));
		//$params['SUCCESSLINK'] = $this->returnUrl;

		// customer information
		$params['billing_cust_name'] = $this->details->getName();
		$params['billing_cust_address'] = $this->details->address->get();
		$params['billing_cust_city'] = $this->details->city->get();
		$params['billing_cust_state'] = $this->details->state->get();
		$params['billing_zip_code'] = $this->details->postalCode->get();
		$params['billing_zip'] = $this->details->postalCode->get();
		$params['billing_cust_country'] = $this->details->country->get();
		$params['billing_cust_email'] = $this->details->email->get();
		$params['billing_cust_tel'] = $this->details->phone->get();

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		return 'https://www.ccavenue.com/shopzone/cc_details.jsp?' . implode('&', $pairs);
	}

	public function notify($requestArray)
	{
		file_put_contents(ClassLoader::getRealPath('cache.') . get_class($this) . '.php', var_export($array, true));
		$Checksum = $this->verifyChecksum($requestArray['Merchant_Id'], $requestArray['Order_Id'], $requestArray['Amount'], $requestArray['AuthDesc'], $requestArray['Checksum'], $this->getConfigValue('WorkingKey'));
		if($Checksum == "true" && in_array($requestArray['AuthDesc'], array('Y', 'B')))
		{
			$result = new TransactionResult();
			$result->gatewayTransactionID->set($requestArray['-']);
			$result->amount->set($requestArray['Amount']);
			$result->currency->set('INR');
			$result->rawResponse->set($requestArray);
			$result->setTransactionType(TransactionResult::TYPE_SALE);
		}
		else
		{
			$result = new TransactionError('Invalid checksum', $requestArray);
		}

		return $result;
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