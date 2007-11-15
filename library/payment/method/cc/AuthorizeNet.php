<?php
include_once(dirname(__file__).'/../../abstract/CreditCardPayment.php');

/**
 *
 * @package library.payment.method.cc
 * @author Integry Systems 
 */
class AuthorizeNet extends CreditCardPayment
{
	private $fields = array();
	
	private $gateway = "https://secure.authorize.net/gateway/transact.dll";	
	
	public function isCreditable()
	{
		return true;
	}
	
	public function isCardTypeNeeded()
	{
		return true;
	}

	public function isVoidable()
	{
		return true;
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
		return 'USD';
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		$this->addField('x_type', 'AUTH_ONLY');
		return $this->process();
	}
	
	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		$this->addField('x_type', 'PRIOR_AUTH_CAPTURE');
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
		$this->addField('x_type', 'AUTH_CAPTURE');
		return $this->process();
	}
	
	private function initHandler()
	{
		$this->addField('x_version', '3.1');
		$this->addField('x_delim_data', 'True');
		$this->addField('x_delim_char', '|');
		$this->addField('x_encap_char', '"');
		$this->addField('x_relay_response', 'False');
		$this->addField('x_login', $this->getConfigValue('login'));
		$this->addField('x_tran_key', $this->getConfigValue('transactionKey'));
								
		$this->addField('x_amount', $this->details->amount->get());
		$this->addField('x_currency_code', $this->details->currency->get());
		$this->addField('x_card_num', $this->getCardNumber());
		$this->addField('x_exp_date', $this->getExpirationMonth() . '/' . $this->getExpirationYear());
		
		if ($this->getConfigValue('test'))
		{
			$this->addField('x_test_request', 'True');
		}

		if ($this->getConfigValue('gateway'))
		{
			$this->gateway = $this->getConfigValue('gateway');
		}
		
		// customer information
		$this->addField('x_cust_id', $this->details->clientID->get());
		$this->addField('x_customer_ip', $this->details->ipAddress->get());
		$this->addField('x_email', $this->details->email->get());
		$this->addField('x_phone', $this->details->phone->get());
		$this->addField('x_first_name', $this->details->firstName->get());
		$this->addField('x_last_name', $this->details->lastName->get());
		$this->addField('x_company', $this->details->companyName->get());
		$this->addField('x_address', $this->details->address->get());
		$this->addField('x_city', $this->details->city->get());
		$this->addField('x_state', $this->details->state->get());
		$this->addField('x_zip', $this->details->postalCode->get());
		$this->addField('x_country', $this->details->country->get());
		
		// order information
		$this->addField('x_invoice_num', $this->details->invoiceID->get());		
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
			$this->addField('x_trans_id', $this->details->gatewayTransactionID->get());
		}
		
		// construct the fields string to pass to authorize.net
		$fields = array();
		foreach ($this->fields as $key => $value) 
		{
			$fields[] = $key . '=' . urlencode($value);
		}
		
		// execute the HTTPS post via CURL
		$ch = curl_init($this->gateway); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $fields)); 
		
		$response = urldecode(curl_exec($ch)); 
		
		if (curl_errno($ch)) 
		{
			return new TransactionError(curl_error($ch), $this->fields);
		}
		
		curl_close ($ch);
		
		$keys= array ( 
		   "Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text",
		   "Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description",
		   "Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name",
		   "Cardholder Last Name", "Company", "Billing Address", "City", "State",
		   "Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name",
		   "Ship to Company", "Ship to Address", "Ship to City", "Ship to State",
		   "Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount",
		   "Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code",
		   "Cardholder Authentication Verification Value (CAVV) Response Code"
		);
		
		$values = $this->getCsvValues($response, '|');
				
		// add additional keys for reserved fields and merchant defined fields
		for ($i = 0; $i <= 27; $i++) 
		{
			array_push($keys, 'Reserved Field ' . $i);
		}
		
		$i = 0;
		while (count($keys) < count($values)) 
		{
			array_push($keys, 'Merchant Defined Field ' . ++$i);
		}
	   
		$result = array_combine($keys, $values);
		
		// declined transaction
		if (3 == $result['Response Code'] || in_array($result['Response Reason Code'], array(311)))
		{
			return new TransactionError($result['Response Reason Text'], $result);
		}
		
		// prepare TransactionResult object
		$res = new TransactionResult();
		$res->gatewayTransactionID->set($result['Transaction ID']);
		$res->amount->set($result['Amount']);
		$res->currency->set('USD');
		$res->AVSaddr->set(in_array($result['AVS Result Code'], array('A', 'X', 'Y')));
		$res->AVSzip->set(in_array($result['AVS Result Code'], array('W', 'X', 'Y', 'Z')));
		$res->CVVmatch->set('M' == $result['AVS Result Code']);
		$res->rawResponse->set($result);
		
		switch ($result['Transaction Type'])
		{
			case 'auth_capture': 
				$res->setTransactionType(TransactionResult::TYPE_SALE);
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
	
	private function getCSVValues($string, $separator = ",")
	{
		$elements = explode($separator, $string);
		for ($i = 0; $i < count($elements); $i++) {
			$nquotes = substr_count($elements[$i], '"');
			if ($nquotes %2 == 1) {
				for ($j = $i+1; $j < count($elements); $j++) {
					if (substr_count($elements[$j], '"') > 0) {
						// Put the quoted string's pieces back together again
						array_splice($elements, $i, $j-$i+1,
							implode($separator, array_slice($elements, $i, $j-$i+1)));
						break;
					}
				}
			}
			if ($nquotes > 0) {
				// Remove first and last quotes, then merge pairs of quotes
				$qstr =& $elements[$i];
				$qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
				$qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
				$qstr = str_replace('""', '"', $qstr);
			}
		}
		return $elements;
	}
}
	
?>