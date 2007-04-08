<?php

include_once('abstract/CreditCardPayment.php');

class AuthorizeNet extends CreditCardPayment
{
	protected $gatewayUrl = 'https://secure.authorize.net/gateway/transact.dll';
	
	public function isCreditable()
	{
		return true;
	}
	
	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		return $this->process('AUTH_ONLY');
	}
	
	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->process('CAPTURE_ONLY');		
	}
	
	/**
	 *	Credit (a part) of customers payment
	 */
	public function credit()
	{
		return $this->process('CREDIT');		
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->process('AUTH_CAPTURE');				
	}
	
	protected function process($type)
	{
		$data = $this->getTransactionData();
		$data['x_type'] = $type;
		
		$dataPairs = array();
		foreach ($data as $key => $value)
		{
			$dataPairs[] = $key . '=' . urlencode($value);
		}
		$passedData = implode('&', $dataPairs);
		
		$ch = curl_init($this->gatewayUrl); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $passedData); 		
		$response = urldecode(curl_exec($ch)); 
		
		if (curl_errno($ch)) 
		{
			throw new PaymentException(curl_error($ch));
		}
		else
		{
			curl_close($ch);			
		} 
	  		
	  	die($response);	  	
	}
	
	protected function getTransactionData()
	{
		$dataMap = array(
		
			'x_first_name' 	=> 'firstName',
			'x_last_name' 	=> 'lastName',
			'x_company' 	=> 'companyName',
			'x_address' 	=> 'address',
			'x_city' 		=> 'city',
			'x_state' 		=> 'state',
			'x_zip' 		=> 'postalCode',
			'x_country' 	=> 'country',
			'x_phone' 		=> 'phone',
			'x_email'		=> 'email',
			
			'x_cust_id' 	=> 'clientID',
			'x_customer_ip' => 'ipAddress',			
			
			'x_invoice_num'	=> 'invoiceID',
			'x_description'	=> 'description',
					
			'x_ship_to_first_name' => 'shippingFirstName',
			'x_ship_to_last_name' => 'shippingLastName',
			'x_ship_to_company' => 'shippingCompanyName',
			'x_ship_to_address' => 'shippingAddress',
			'x_ship_to_city' => 'shippingCity',
			'x_ship_to_state' => 'shippingState',
			'x_ship_to_zip' => 'shippingPostalCode',
			'x_ship_to_country' => 'shippingCountry',

			'x_amount' => 'amount',
			'x_currency_code' => 'currency',
		
		);		
	
		$data = array();
		foreach ($dataMap as $key => $dataKey)
		{			
			if ($value = $this->transactionDetails->getValue($dataKey))
			{
				$data[$key] = $value;
			}
		}
		
		$data['x_method'] = 'CC';
		
		$data['x_delim_data'] = 'TRUE';
		$data['x_delim_char'] = chr(9);
		$data['x_delim_data'] = '"';
		$data['x_relay_response'] = 'FALSE';
								
		return $data;
	}
	
}
	
?>