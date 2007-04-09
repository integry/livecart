<?php

include_once('../abstract/CreditCardPayment.php');
include_once('../method/paypal/PaypalCommon.php');

class PaypalDirectPayment extends CreditCardPayment
{
	public static function isCreditable()
	{
		return false;
	}
	
	public static function isVoidable()
	{
		return true;
	}
	
	public static function getSupportedCurrencies()
	{
		return array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD');
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		return $this->processAuth('DoDirectPayment', 'Authorization');
	}
	
	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->processCapture();
	}
	
	/**
	 *	Credit (a part) of customers payment
	 */
	public function credit()
	{
		return $this->process('');		
	}

	/**
	 *	Void the payment (issue full credit)
	 */
	public function void()
	{
		return $this->processVoid();		
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->processAuth('DoDirectPayment', 'Sale');
	}
	
	protected function processAuth($api, $type)
	{		
		$paypal = $this->getHandler($api);
	
		$address = PayPalTypes::AddressType(
				
						$this->details->firstName->get() . ' ' . $this->details->lastName->get(), 
						$this->details->address->get(), '' /* address 2 */,
						$this->details->city->get(), $this->details->state->get(),
						$this->details->postalCode->get(), $this->details->country->get(),
						$this->details->phone->get()
				
					);
				
		$personName = PayPalTypes::PersonNameType('', $this->details->firstName->get(), '', $this->details->lastName->get());

		$payerInfo = PayPalTypes::PayerInfoType($this->details->email->get(), $this->details->clientID->get(), 'verified', $personName, $this->details->country->get(), '', $address);

		$creditCardDetails = PayPalTypes::CreditCardDetailsType($this->getCardType(), $this->getCardNumber(), $this->getExpirationMonth(), $this->getExpirationYear(), $payerInfo, $this->getCardCode());

		$paymentDetails = PayPalTypes::PaymentDetailsType($this->details->amount->get(), $this->details->amount->get(), 0, 0, 0, $this->details->description->get(), $this->details->clientID->get(), $this->details->invoiceID->get(), '', 'ipn_notify.php', $address, array(), $this->details->currency->get());

		$paypal->setParams($type, $paymentDetails, $creditCardDetails, $this->details->ipAddress->get(), session_id());

		$paypal->execute($api);

		if ($paypal->success())
		{
		    $response = $paypal->getAPIResponse();
		    
		    if (isset($response->Errors))
		    {
				if (isset($response->Errors->LongMessage))
				{
					$error = $response->Errors;
				}
				else
				{
					$error = $response->Errors[0];
				}
			
				return new TransactionError($error->LongMessage, $response);
			}
			else
			{
				$result = new TransactionResult();
				$result->gatewayTransactionID->set($response->TransactionID);
				$result->amount->set($response->Amount->_);
				$result->currency->set($response->Amount->currencyID);
				
				$avs = PaypalCommon::getAVSbyCode($response->AVSCode);
				$result->AVSaddr->set($avs[0]);
				$result->AVSzip->set($avs[1]);

				$result->CVVmatch->set(PaypalCommon::getCVVByCode($response->CVV2Code));
				
				$result->rawResponse->set($response);
												
				return $result;
			}
		}
		else
		{
		    return $paypal->getAPIException();
		}		
	}
	
	protected function processCapture()
	{
		$paypal = $this->getHandler('DoCapture');
		$paypal->setParams($this->details->gatewayTransactionID->get(), $this->details->amount->get(), $this->details->currency->get(), 'NotComplete', '', $this->details->invoiceID->get());
		
		$paypal->execute();
		
		if ($paypal->success())
		{
		    $response = $paypal->getAPIResponse();

		    if (isset($response->Errors))
		    {
				return new TransactionError($response->Errors->LongMessage, $response);
			}
			else
			{
				$result = new TransactionResult();

				$details = $response->DoCaptureResponseDetails->PaymentInfo;
			
				$result->gatewayTransactionID->set($details->TransactionID);
				$result->amount->set($details->GrossAmount->_);
				$result->currency->set($details->GrossAmount->currencyID);

				$result->rawResponse->set($response);

				return $result;
			}
		}
		else
		{
		    return $paypal->getAPIException();
		}			
	}
	
	protected function processVoid()
	{
		$paypal = $this->getHandler('DoVoid');
		$paypal->setParams($this->details->gatewayTransactionID->get(), '');
		
		$paypal->execute();
		
		if ($paypal->success())
		{
		    $response = $paypal->getAPIResponse();

		    if (isset($response->Errors))
		    {
				return new TransactionError($response->Errors->LongMessage, $response);
			}
			else
			{
				$result = new TransactionResult();

				$result->rawResponse->set($response);

				return $result;
			}
		}
		else
		{
		    return $paypal->getAPIException();
		}		
	}
	
	protected function getHandler($api)
	{
		set_time_limit(0);
		
		$handler = new WebsitePaymentsPro();
		
		$username = 'sandbox_api1.integry.net';		
		$password = '9AURF7SPQCEYCDXV';		
		$signature = 'AeQ618dBMNS1kVFZwUIitcve-k.dAT5pnzBekoPUhcIj1J5p65ZAR8Pu';
		
		$handler->prepare($username, $password, $signature);		
		
		$paypal = $handler->selectOperation($api);
		
		return $paypal;		
	}
}
	
?>