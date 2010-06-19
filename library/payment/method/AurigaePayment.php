<?php
//  Payment processor for Auriga ePayment
//
//  by Greger Andersson, Electrokit Sweden AB
//	- for free use with LiveCart

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class AurigaePayment extends ExternalPayment
{
	
	public function getPostParams()
	{
		$params = array();

		// merchant id
		$params['Merchant_id'] = $this->getConfigValue('merchant');
		
		// version
		$params['Version'] = "3";		


		// A seller reference number for a transaction
		$params['Customer_refno'] = $this->details->invoiceID->get();

		// The currency code of the payment amount.
		$params['Currency'] = $this->details->currency->get();

		// The payment amount
		$params['Amount'] = round($this->details->amount->get() * 100);
		
		//$params['VAT'] = 0;
		
		// The payment method
		$params['Payment_method'] = strtoupper($this->getConfigValue('method'));
		
		// Purchase date
		$params['Purchase_date'] = date("YmdHi");

		$this->notifyUrl = preg_replace('/currency\=[A-Z]{3}/', '', $this->notifyUrl);     // remove currency from base URL
		$params['Response_URL'] = $this->notifyUrl;
		$params['Goods_description'] = "Order " . $this->details->invoiceID->get();	
		$params['Language'] = "SWE"; // ActiveRecordModel::getApplication()->getLocaleCode();		
		$params['Country'] = $this->details->country->get();
		$params['Cancel_URL'] = $this->siteUrl;

		$params['MAC'] = $this->getMd5Key($params);

		return $params;
	}
	
	public function getUrl()
	{

		if ($this->getConfigValue('test'))
		{
			//$tmp = 'http://www.electrokit.se/notyet/test.php';
			$tmp = 'https://test-epayment.auriganet.eu/paypagegw';
		}
		else
		{
			$tmp = 'https://epayment.auriganet.eu/paypagegw';
		}
		
		return $tmp;
	}

	private function getMd5Key($params)
	{
				
		return md5( $params['Merchant_id'] . $params['Version'] . $params['Customer_refno'] . 
					$params['Currency'] . $params['Amount'] . 
					$params['Payment_method'] . $params['Purchase_date'] . $params['Response_URL'] .
					$params['Goods_description'] . $params['Language'] . $params['Country'] . 
					$params['Cancel_URL'] . $this->getConfigValue('md5') );			
			
	}
	
	private function getMd5Key_response($params)
	{
		
		$tmp =  md5( $params['Merchant_id'] . $params['Version'] . $params['Customer_refno'] . 
					$params['Transaction_id']  .  $params['Status'] . $params['Status_code'] .
					$params['AuthCode'] . $params['3DSec'] . $params['Batch_id'] . $params['Currency'] . 
					$params['Payment_method'] . $params['Card_num'] . $params['Exp_date'] .
					$params['Card_type'] . $params['Risk_score'] . $params['Ip_country'] .
					$params['Issuing_country'] . $params['Authorized_amount'] . $params['Fee_amount'] . $this->getConfigValue('md5') );	
                    
        return $tmp;
                    		

	}


	public function notify($requestArray)
	{
		file_put_contents(ClassLoader::getRealPath('cache.') . 'notify.php', var_export($requestArray, true));
        $tmp = $this->getMd5Key_response($requestArray);
		if ($requestArray['MAC'] == $tmp)
		{
			$result = new TransactionResult();
			$result->gatewayTransactionID->set($requestArray['Transaction_id']);
			$result->rawResponse->set($requestArray);
			
			$result->amount->set($this->details->amount->get());			// amount not returned in response
			$result->currency->set($requestArray['Currency']);		
			
			if ($requestArray['Status'] == 'A' && $requestArray['Status_code'] == '0')
			{
				$result->setTransactionType(TransactionResult::TYPE_AUTH);
			}
			else
			{
				$result = new TransactionError('Transaction error/denied', $requestArray);
			}

		}
		else
		{
			$result = new TransactionError('md5key mismatch', $requestArray);
		}

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['Customer_refno'];
	}
	
	public function isPostRedirect()   // POST or GET
	{
		return true;
	}

	public function isNotify()
	{
		return true;
	}


	public function isHtmlResponse()
	{
		return false;
	}
	
	private function getCurrency($code)
	{
		return $code;
	}
	
	public static function getSupportedCurrencies()
	{
		return array('CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'NOK', 'SEK', 'USD');
	}
	

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : 'USD';
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
