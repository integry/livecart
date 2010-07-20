<?php

include_once(dirname(__file__).'/../../abstract/CreditCardPayment.php');

/**
 *
 * @package library.payment.method.cc
 * @author Integry Systems
 */
class EWayCvn extends CreditCardPayment
{
	private $fields = array();

	public function isCreditable()
	{
		return false;
	}

	public function isCardTypeNeeded()
	{
		return false;
	}

	public function isVoidable()
	{
		return false;
	}

	public function isMultiCapture()
	{
		return false;
	}

	public function isCapturedVoidable()
	{
		return true;
	}

	public function getValidCurrency($currency)
	{
		return 'AUD';
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		return $this->process();
	}

	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->process();
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
		$this->addField('x_type', 'VOID');
		return $this->process();
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->process();
	}

	private function initHandler()
	{
		$this->addField('ewayCustomerID', $this->getConfigValue('customerID'));
		$this->addField('ewayCustomerInvoiceDescription', $this->getConfigValue('invoiceDescription'));

		$this->addField('ewayTotalAmount', $this->details->amount->get() * 100);
		$this->addField('ewayCardNumber', $this->getCardNumber());
		$this->addField('ewayCardExpiryMonth', $this->getExpirationMonth());
		$this->addField('ewayCardExpiryYear', $this->getExpirationYear());

		if ($this->getCardCode())
		{
			$this->addField('ewayCVN', $this->getCardCode());
		}

		$this->gatewayURL = $this->getConfigValue('test') ? 'https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp' : 'https://www.eway.com.au/gateway_cvn/xmlpayment.asp';

		// customer information
		$this->addField('ewayCustomerEmail', $this->details->email->get());
		$this->addField('ewayCustomerFirstName', $this->details->firstName->get());
		$this->addField('ewayCustomerLastName', $this->details->lastName->get());
		$this->addField('ewayCardHoldersName', $this->details->getName());

		$this->addField('ewayCustomerAddress', $this->details->address->get() . ', ' . $this->details->city->get() . ', ' . $this->details->state->get() . ', ' . $this->details->country->get());
		$this->addField('ewayCustomerPostcode', $this->details->postalCode->get());
		$this->addField('ewayOption1', '');
		$this->addField('ewayOption2', '');
		$this->addField('ewayOption3', '');

		// order information
		$this->addField('ewayTrxnNumber', $this->details->invoiceID->get());
		$this->addField('ewayCustomerInvoiceRef', $this->details->invoiceID->get());

	}

	private function addField($field, $value)
	{
		$this->fields[$field] = $value;
	}

	private function process()
	{
		set_time_limit(0);

		$this->initHandler();

		if ($this->details->gatewayTransactionID->get())
		{
			$this->addField('ewayOriginalTrxnNumber', $this->details->gatewayTransactionID->get());
		}

		$result = $this->doPayment();

		// test gateway
		if (isset($result['EWAYTRXNERROR']) && substr($result['EWAYTRXNERROR'], 0, 3) == '17,')
		{
			unset($result['EWAYTRXNERROR']);
			$result['EWAYTRXNSTATUS'] = true;
		}

		if ($result['EWAYTRXNSTATUS'] === 'False')
		{
			return new TransactionError($result['EWAYTRXNERROR'], $result);
		}
		else
		{
			$res = new TransactionResult();
			$res->gatewayTransactionID->set($result['EWAYTRXNREFERENCE']);
			$res->amount->set($result['EWAYRETURNAMOUNT'] / 100);
			$res->currency->set('AUD');
			$res->rawResponse->set($result);
			$res->setTransactionType(TransactionResult::TYPE_SALE);
		}

		return $res;

		// declined transaction
		if (1 != $result['Response Code'] || in_array($result['Response Reason Code'], array(311)))
		{
			return new TransactionError($result['Response Reason Text'], $result);
		}

		switch (strtolower($result['Transaction Type']))
		{
			case 'auth_capture':

			break;

			case 'auth_only':
				$res->setTransactionType(TransactionResult::TYPE_AUTH);
			break;

			case 'prior_auth_capture':
				$res->setTransactionType(TransactionResult::TYPE_CAPTURE);
			break;

			case 'void':
				$res->setTransactionType(TransactionResult::TYPE_VOID);
			break;

			case 'credit':
				$res->setTransactionType(TransactionResult::TYPE_REFUND);
			break;

			default:
				throw new PaymentException('Transaction type "' . $result['Transaction Type'] . '" is not implemented');
			break;
		}

		return $res;
	}

	//Payment Function
	private function doPayment()
	{
		$xmlRequest = "<ewaygateway>";
		foreach($this->fields as $key=>$value)
			$xmlRequest .= "<$key>" . htmlspecialchars($value) . "</$key>";
        $xmlRequest .= "</ewaygateway>";

		$xmlResponse = $this->sendTransactionToEway($xmlRequest);

		if($xmlResponse != "")
		{
			return $this->parseResponse($xmlResponse);
		}
       	else
       	{
       		die("Error in XML response from eWAY: " . $xmlResponse);
		}
	}

	//Send XML Transaction Data and receive XML response
	private function sendTransactionToEway($xmlRequest)
	{
		$ch = curl_init($this->gatewayURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $xmlResponse = curl_exec($ch);

        if(curl_errno( $ch ) == CURLE_OK)
        {
        	return $xmlResponse;
        }
	}

	//Parse XML response from eway and place them into an array
	private function parseResponse($xmlResponse)
	{
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser,  $xmlResponse, $xmlData, $index);
        $responseFields = array();
        print_r($xmlData);
        foreach($xmlData as $data)
       	{
	    	if(($data["level"] == 2) && isset($data['value']))
	    	{
        		$responseFields[$data["tag"]] = $data["value"];
        	}
        }
        return $responseFields;
	}
}

?>