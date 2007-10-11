<?php

include_once(dirname(__file__) . '/../../abstract/CreditCardPayment.php');
include_once(dirname(__file__) . '/../../method/library/paypal/PaypalCommon.php');

/**
 *
 * @package library.payment.method.cc
 * @author Integry Systems 
 */
class PaypalDirectPayment extends CreditCardPayment
{
	public function isCreditable()
	{
		return false;
	}
	
	public function isVoidable()
	{
		return true;
	}
	
	public function isCardTypeNeeded()
	{
        return true;
    }
	
	public function isMultiCapture()
	{
        return true;
    }

	public function isCapturedVoidable()
	{
        return false;
    }

    public function getValidCurrency($currentCurrencyCode)
    {
        $currentCurrencyCode = strtoupper($currentCurrencyCode);
        return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : 'USD';
    }

    public function getSupportedCurrencies()
    {
        return array('CAD', 'EUR', 'GBP', 'USD', 'JPY', 'AUD');
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
				$error = isset($response->Errors->LongMessage) ? $response->Errors : $error = $response->Errors[0];
			
				return new TransactionError($error->LongMessage, $response);
			}
			else
			{
				$result = new TransactionResult();
				$result->gatewayTransactionID->set($response->TransactionID);
				$result->amount->set($response->Amount);
				$result->currency->set($response->Currency);
				
				$avs = PaypalCommon::getAVSbyCode($response->AVSCode);
				$result->AVSaddr->set($avs[0]);
				$result->AVSzip->set($avs[1]);

				$result->CVVmatch->set(PaypalCommon::getCVVByCode($response->CVV2Code));
				
				$result->rawResponse->set($response);
					
                if ('Sale' == $type)
                {
                    $result->setTransactionType(TransactionResult::TYPE_SALE);
                }
                else
                {
                    $result->setTransactionType(TransactionResult::TYPE_AUTH);
                }
                    							
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
		return PaypalCommon::processCapture($this);
	}
	
	protected function processVoid()
	{
		return PaypalCommon::processVoid($this);
	}
	
	public function getHandler($api)
	{
		PayPalBase::$isLive = !$this->getConfigValue('sandbox');

		return PaypalCommon::getHandler($this, $api);
	}
}
	
?>