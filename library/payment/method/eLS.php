<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */

define('DEBUG_OID',1);
class eLS extends ExternalPayment
{
	public function getUrl()
	{
		return 'https://www.e-ls.lv/ccx';
	}

	public function getPostParams()
	{
		$parameters = array();
		$parameters['mid'] = $this->getConfigValue('Account');
		$parameters['oid'] = $this->details->invoiceID->get().DEBUG_OID;
		$parameters['a'] = round($this->details->amount->get() * 100);
		$parameters['cur'] = $this->getValidCurrency($this->details->currency->get());
		$parameters['product_name'] = "Order " . $this->details->invoiceID->get();
		$parameters['d'] = '';
		$parameters['e'] = $this->details->email->get();
		$parameters['mp'] = $this->details->phone->get();
		$parameters['c_phone'] = $this->details->phone->get();
		$parameters['lang'] = ActiveRecordModel::getApplication()->getLocaleCode();
		$parameters['co'] = strtolower($this->details->country->get());
		$parameters['c_first_name'] = $this->details->firstName->get();
		$parameters['c_last_name'] = $this->details->lastName->get();
		list($address1, $address2) = $this->splitAddress($this->details->address->get());
		$parameters['c_add_1'] = $address1;
		$parameters['c_add_2'] = $address2;
		$parameters['c_city'] =  $this->details->city->get();
		$parameters['c_state'] = $this->details->state->get();
		$parameters['c_zip'] = $this->details->postalCode->get();
		$parameters['s_first_name'] = $this->details->shippingFirstName->get();
		$parameters['s_last_name'] = $this->details->shippingLastName->get();
		list($address1, $address2) = $this->splitAddress($this->details->shippingAddress->get());
		$parameters['s_add_1'] = $address1;
		$parameters['s_add_2'] = $address2;
		$parameters['s_city'] =  $this->details->shippingCity->get();
		$parameters['s_state'] = $this->details->shippingState->get();
		$parameters['s_zip'] = $this->details->shippingPostalCode->get();

		return $parameters;
	}

	private function splitAddress($address)
	{
		// in LiveCartTransaction address1 and address2 are merged..
		// but here need address with 2 lines.
		$address1 = '';
		$address2 = '';
		$parts = explode(' ', $address);
		$parts = array_filter($parts);
		$c = count($parts);
		if($c > 0)
		{
			$address2 = implode(' ', array_splice($parts, ceil($c/2)));
			$address1 = implode(' ',$parts);
		}
		return array($address1, $address2);
	}

	public function notify($requestArray)
	{
		/*
			http://192.168.1.3/copy/checkout/notify/eLS?cs2=d18a97ce84eb0e529c65c4c40e9d5072fb2c4289&lang=en&oid=1003197&tid=10081174210029617634131281085849&interface=ccx&timedate=2211452522&
		*/
		$requestArray['status'] = 'FAILURE'; // default response status
		$amount = round($this->details->amount->get() * 100);
		$currency = $this->getValidCurrency($this->details->currency->get());
		$hashBeginning = $this->getConfigValue('Account') .'/'.$requestArray['oid'].'/'.$amount.'/'.$currency.'/'.$this->getConfigValue('SecurityCode').'/';
		foreach(array('SUCCESS', 'FAILURE', 'REVERSED', 'CHARGEBACKED', 'PENDING', 'STARTED') as $status)
		{
			if (sha1($hashBeginning.$status) == $requestArray['cs2'])
			{
				$requestArray['status'] = $status;
			}
		}

		switch($requestArray['status'])
		{
			case 'SUCCESS':
				$result = new TransactionResult();
				$result->gatewayTransactionID->set($requestArray['tid']);
				$result->amount->set($amount);
				$result->currency->set($currency);
				$result->rawResponse->set($requestArray);
				$result->setTransactionType(TransactionResult::TYPE_SALE);
				break;

			case 'STARTED':
				$result = TransactionError('Transaction started', $requestArray);

			case 'PENDING':
				$result = TransactionError('Transaction pending', $requestArray);
				break;

			default:
				$result = TransactionError('Transaction declined', $requestArray);
		}

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['oid'];
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
		return false;
	}

	private function getCurrency($code)
	{
		return $code;
	}

	public static function getSupportedCurrencies()
	{
		return array('LVL', 'EUR', 'USD');
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		if (!in_array($currentCurrencyCode, self::getSupportedCurrencies()))
		{
			return 'LVL';
			// throw new Exception('Unsupported currency code: '.$currentCurrencyCode);
		}
		return $currentCurrencyCode;
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