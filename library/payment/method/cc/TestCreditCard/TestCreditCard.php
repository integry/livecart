<?php

include_once(dirname(__file__) . '/../../../abstract/CreditCardPayment.php');

class TestCreditCard extends CreditCardPayment
{
	public static function isCreditable()
	{
		return true;
	}
	
	public static function isVoidable()
	{
		return true;
	}
	
	public static function getSupportedCurrencies()
	{
		return array('AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD');
	}

	public static function isCurrencySupported($currencyCode)
	{
		return true;
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
		return $this->process();		
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->process('Sale');
	}
	
	public function toArray()
	{
		$ret = parent::toArray();
		$ret['name'] = 'Test';
		$ret['cardTypes'] = array(self::TYPE_VISA, self::TYPE_MC, self::TYPE_AMEX);
		return $ret;
	}
	
	private function process($type)
	{		
		if ($this->getCardCode() == '000')
		{
            return new TransactionError($this->details, '');    
        }
        
        $result = new TransactionResult();
		$result->gatewayTransactionID->set('TESTCC' . rand(1, 10000000));
		$result->amount->set($this->details->amount->get());
		$result->currency->set($this->details->currency->get());
		
		$result->AVSaddr->set(true);
		$result->AVSzip->set(true);
		$result->CVVmatch->set(true);	
		
		$result->setAsCaptured();
		
		return $result;		
	}
}
	
?>