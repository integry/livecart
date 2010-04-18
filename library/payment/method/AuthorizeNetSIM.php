<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class AuthorizeNetSIM extends ExternalPayment
{
	public function getUrl()
	{
		$params['x_version'] = '3.1';
		$params['x_login'] = $this->getConfigValue('login');
		$params['x_show_form'] = 'PAYMENT_FORM';
		$params['x_amount'] = $this->details->amount->get();
		$params['x_relay_response'] = 'TRUE';
		$params['x_relay_url'] = $this->notifyUrl;
		$params['x_type'] = 'AUTH_CAPTURE';
		$params['x_fp_sequence'] = rand(1, 1000);
		$params['x_fp_timestamp'] = time();

		$params['x_fp_hash'] = hash_hmac('md5', $params['x_login'] . "^" . $params['x_fp_sequence'] . "^" . $params['x_fp_timestamp'] . "^" . $params['x_amount'] . "^", $this->getConfigValue('transKey'));

		// order information
		$params['x_invoice_num'] = $this->details->invoiceID->get();

		// customer information
		$params['x_first_name'] = $this->details->firstName->get();
		$params['x_last_name'] = $this->details->lastName->get();
		$params['x_company'] = $this->details->companyName->get();
		$params['x_address'] = $this->details->address->get();
		$params['x_city'] = $this->details->city->get();
		$params['x_state'] = $this->details->state->get();
		$params['x_zip'] = $this->details->postalCode->get();
		$params['x_country'] = $this->details->country->get();
	 /* $params['x_email'] = $this->details->email->get(); */
		$params['x_phone'] = $this->details->phone->get();

		$params['x_ship_to_first_name'] = $this->details->shippingFirstName->get();
		$params['x_ship_to_last_name'] = $this->details->shippingLastName->get();
		$params['x_ship_to_company'] = $this->details->shippingCompanyName->get();
		$params['x_ship_to_address'] = $this->details->shippingAddress->get();
		$params['x_ship_to_city'] = $this->details->shippingCity->get();
		$params['x_ship_to_state'] = $this->details->shippingState->get();
		$params['x_ship_to_zip'] = $this->details->shippingPostalCode->get();
		$params['x_ship_to_country'] = $this->details->shippingCountry->get();

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		if ($this->getConfigValue('test'))
		{
			return 'https://test.authorize.net/gateway/transact.dll?' . implode('&', $pairs);
		}
		else
		{
			return 'https://secure.authorize.net/gateway/transact.dll?' . implode('&', $pairs);
		}
	}

	public function notify($requestArray)
	{
		$this->saveDebug($requestArray);
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['x_trans_id']);
		$result->amount->set($requestArray['x_amount']);
		$result->currency->set('USD');
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['x_invoice_num'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return '';
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return 'USD';
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
