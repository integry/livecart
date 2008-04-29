<?php

include_once(dirname(__file__) . '/../../abstract/CreditCardPayment.php');

/**
 *
 * @package library.payment.method.cc
 * @author Integry Systems
 */
class OfflineCreditCard extends CreditCardPayment
{
	public function isCardNumberStored()
	{
		return true;
	}

	public function isCreditable()
	{
		return false;
	}

	public function isCardTypeNeeded()
	{
		return true;
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
		return false;
	}

	/**
	 *	All currencies supported
	 */
	public function getValidCurrency($currentCurrencyCode)
	{
		return $currentCurrencyCode;
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		$result = $this->process('Sale');
		if ($result instanceof TransactionResult)
		{
			$result->setTransactionType(TransactionResult::TYPE_AUTH);
		}

		return $result;
	}

	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		$result = $this->process('Capture');
		if ($result instanceof TransactionResult)
		{
			$result->setTransactionType(TransactionResult::TYPE_CAPTURE);
		}

		return $result;
	}

	/**
	 *	Credit (a part) of customers payment
	 */
	public function credit()
	{
		$result = $this->process('');
		if ($result instanceof TransactionResult)
		{
			$result->setTransactionType(TransactionResult::TYPE_VOID);
		}

		return $result;
	}

	/**
	 *	Void the payment (issue full credit)
	 */
	public function void()
	{
		$result = $this->process('');
		if ($result instanceof TransactionResult)
		{
			$result->setTransactionType(TransactionResult::TYPE_VOID);
		}
		return $result;
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		$result = $this->process('Sale');
		if ($result instanceof TransactionResult)
		{
			$result->setTransactionType(TransactionResult::TYPE_SALE);
		}

		return $result;
	}

	public function toArray()
	{
		$ret = parent::toArray();
		$ret['name'] = 'Offline';
		return $ret;
	}

	private function isCardNumberValid($number)
	{
		# Double every second digit started at the righ
		$doubledNumber  = "";
		$odd            = false;
		for($i = strlen($number)-1; $i >=0; $i--)
		{
			$doubledNumber .= ($odd) ? $number[$i]*2 : $number[$i];
			$odd            = !$odd;
		}

		# Add up each 'single' digit
		$sum = 0;
		for($i = 0; $i < strlen($doubledNumber); $i++)
			$sum += (int)$doubledNumber[$i];

		# A valid number doesn't have a remainder after mod10\
		# or equal to 0
		return (($sum % 10 ==0) && ($sum != 0));
	}

	private function process($type)
	{
		if (($type == 'Sale') && !$this->isCardNumberValid($this->getCardNumber()))
		{
			return new TransactionError($this->details, '');
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set('OFFLINE' . strtoupper(uniqid()));
		$result->amount->set($this->details->amount->get());
		$result->currency->set($this->details->currency->get());

		$result->AVSaddr->set(null);
		$result->AVSzip->set(null);
		$result->CVVmatch->set(null);

		return $result;
	}
}

?>