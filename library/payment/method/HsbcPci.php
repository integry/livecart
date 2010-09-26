<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');
include_once(dirname(__file__) . '/library/hsbccpi/orderCrypto.php');
include_once(dirname(__file__) . '/library/iso/ISO3166.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class HsbcPci extends ExternalPayment
{
	public function getUrl()
	{
		return 'https://www.cpi.hsbc.com/servlet';
	}

	public function getPostParams()
	{
		$params = array();
		$params['OrderId'] = $this->details->invoiceID->get();;
		$params['TimeStamp'] = time() * 1000; // !miliseconds
		$params['CpiReturnUrl'] = $this->returnUrl;
		$params['CpiReturnUrl'] = $this->notifyUrl;
		$params['CpiDirectResultUrl'] = $this->notifyUrl;
		$params['StorefrontId'] = $this->getConfigValue('client');
		$params['OrderDesc'] = 'Order '. $params['OrderId'];
		$amount = $this->details->amount->get();
		$currency = $this->details->currency->get();
		//switch($currency) // by number of digits after decimal seperator
		//{
			// case '':
			// $amount = $amount * 1000;
			//default:
				$amount = $amount * 100;
		//}
		$params['PurchaseAmount'] = $amount;
		$params['PurchaseCurrency'] = self::currencyToNumeric3($currency);
		$params['TransactionType'] = $this->getConfigValue('capture_automatically') ? 'Capture' : 'Auth';

		$user = $this->details->getOrder()->userID->get();
		// $params['UserId'] = $user->getID();

		$params['Mode'] =  $this->getConfigValue('test') ? 'T' : 'P';
		$params['MerchantData'] =  '';

		$params['BillingFirstName'] = $this->details->firstName->get();
		$params['BillingLastName'] = $this->details->lastName->get();
		$params['ShopperEmail'] = $user->email->get();
		$params['BillingAddress1'] = $this->details->address->get();
		$params['BillingAddress2'] = '';

		$params['BillingCity'] = $this->details->city->get();
		$country = ISO3166::getCountry($this->details->country->get());
		$params['BillingCounty'] = $country['Name'];
		$params['BillingPostal'] = $this->details->postalCode->get();
		$params['BillingCountry'] = $country['Numeric3'];
		$params['ShippingFirstName'] = $this->details->shippingFirstName->get();
		$params['ShippingLastName'] = $this->details->shippingLastName->get();
		$params['ShippingAddress1'] = $this->details->shippingAddress->get();
		$params['ShippingAddress2'] = '';
		$params['ShippingCity'] = $this->details->shippingCity->get();
		$country = ISO3166::getCountry($this->details->shippingCountry->get());
		$params['ShippingCounty'] = $country['Name'];
		$params['ShippingPostal'] = $this->details->shippingPostalCode->get();
		$params['ShippingCountry'] = $country['Numeric3'];
		$params['OrderHash'] = generateHash(array_values($params), $this->getConfigValue('key'));
		//$this->debug('getPostParams()', $params);
		return $params;
	}

	public function notify($requestArray)
	{
		// $this->debug('notify()', $requestArray);
		if(array_key_exists('CpiResultsCode', $requestArray) == false || $requestArray['CpiResultsCode'] != 0 || array_key_exists('OrderHash', $requestArray) == false)
		{
			return new TransactionError('Transaction declined', $requestArray);
		}
		$data = $_POST;
		unset($data['OrderHash']);
		if($requestArray['OrderHash'] != generateHash(array_values($data), $this->getConfigValue('key')))
		{
			return new TransactionError('Transaction declined', $requestArray);
		}
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['OrderId']);
		$result->amount->set($requestArray['PurchaseAmount'] / 100);
		$result->currency->set(self::currencyFromNumeric3($requestArray['PurchaseCurrency']));
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);
		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['OrderId'];
	}

	public function isPostRedirect()
	{
		return true;
	}

	public function isNotify()
	{
		return true;
	}

	public function isHtmlResponse()
	{
		return true;
	}

	private function getCurrency($code)
	{
		return $code;
	}
	
	public static function currencyToNumeric3($alpha3)
	{
		$m = array('GBP'=> 826, 'EUR' => 978, 'USD' => 840);
		return array_key_exists($alpha3, $m) ? $m[$alpha3] : null;
	}

	public static function currencyFromNumeric3($numeric3)
	{
		$m = array('826' => 'GBP', '978' =>'EUR', '840' =>'USD');
		return array_key_exists($numeric3, $m) ? $m[$numeric3] : null;
	}


	public static function getSupportedCurrencies()
	{
		return array($this->getConfigValue('currency'));
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return $this->getConfigValue('currency');
	}

	public function isVoidable()
	{
		return false;
	}

	private function debug($str, $data)
	{
		$fn = ClassLoader::getRealPath('cache.') . 'debug.log';
		file_put_contents($fn,(file_exists($fn) ? file_get_contents($fn) : '')."\n".date('Y-m-d H:i:s').' '.$str.":\n".var_export($data, true)."\n------");
	}
}

?>