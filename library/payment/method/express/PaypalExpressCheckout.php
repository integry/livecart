<?php

include_once(dirname(__file__) . '/../../abstract/ExpressPayment.php');
include_once(dirname(__file__) . '/../../method/library/paypal/PaypalCommon.php');

class PaypalExpressCheckout extends ExpressPayment
{
	protected $data;
	
    public function getInitUrl($returnUrl, $cancelUrl, $sale = true)
	{
        $paypal = $this->getHandler('SetExpressCheckout');
        $paypal->setParams($this->details->amount->get(), $returnUrl, $cancelUrl, $sale ? 'Sale' : 'Order');
        $paypal->execute();

        $this->checkErrors($paypal);

    	$response = $paypal->getAPIResponse();
        $sandbox = substr($this->getConfigValue('username'), 0, 8) == 'sandbox_' ? 'sandbox.' : '';        
        return 'Location: https://www.' . $sandbox . 'paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $response->Token;
    }
    
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function getTransactionDetails($request = null)
    {        
        $paypal = $this->getHandler('GetExpressCheckoutDetails');
        $paypal->setParams($request ? $request['token'] : $this->data['token']);
        $paypal->execute();

        $this->checkErrors($paypal);

        $response = $paypal->getAPIResponse();
        $info = $response->GetExpressCheckoutDetailsResponseDetails->PayerInfo;
        $valueMap = array(
        
        		'firstName' => $info->PayerName->FirstName,
        		'lastName' => $info->PayerName->LastName,
        		'companyName' => $info->PayerBusiness,
        		
        		'address' => $info->Address->Street1 . ($info->Address->Street2 ? ', ' . $info->Address->Street2 : ''),
        		'city' => $info->Address->CityName,
        		'state' => $info->Address->StateOrProvince,
        		'country' => $info->Address->Country,
        		'postalCode' => $info->Address->PostalCode,
        	
        		'email' => $info->Payer,	
        		
        		// customer data
        		'clientID' => $info->PayerID,
            );
        
        foreach ($valueMap as $key => $value)
        {
            $this->details->$key->set($value);
        }
        
        return $this->details;
    }
    
    private function checkErrors($paypal)
    {
        if ($paypal->success())
        {
        	$response = $paypal->getAPIResponse();
            if (isset($response->Errors))
            {
                throw new PaymentException($response->Errors->LongMessage);
            }
        }         
        else
        {
            throw new PaymentException($paypal->getAPIException()->getMessage());
        }
    }
    
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
		return $this->processAuth('DoExpressCheckoutPayment', 'Order');
	}
	
	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->processCapture();
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
		return $this->processAuth('DoExpressCheckoutPayment', 'Sale');
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

		$paymentDetails = PayPalTypes::PaymentDetailsType($this->details->amount->get(), $this->details->amount->get(), 0, 0, 0, $this->details->description->get(), $this->details->clientID->get(), $this->details->invoiceID->get(), '', 'ipn_notify.php', '', array(), $this->details->currency->get());

		$paypal->setParams($type, $this->data['token'], $this->data['PayerID'], $paymentDetails);

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
				$response = $response->DoExpressCheckoutPaymentResponseDetails->PaymentInfo;
				
                $result = new TransactionResult();

				$result->gatewayTransactionID->set($response->TransactionID);
				$result->amount->set($response->GrossAmount->_);
				$result->currency->set($response->GrossAmount->currencyID);
								
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
		$paypal = $this->getHandler('DoCapture');
		$paypal->setParams($this->details->gatewayTransactionID->get(), $this->details->amount->get(), $this->details->currency->get(), $this->details->isCompleted->get() ? 'Complete' : 'NotComplete', '', $this->details->invoiceID->get());
		
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
                $result->setTransactionType(TransactionResult::TYPE_CAPTURE);

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
                $result->setTransactionType(TransactionResult::TYPE_VOID);

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
		
		$username = $this->getConfigValue('username');		
		$password = $this->getConfigValue('password');
		$signature = $this->getConfigValue('signature');
		
		$handler->prepare($username, $password, $signature);		
		
		$paypal = $handler->selectOperation($api);
		
		return $paypal;		
	}
}
	
?>