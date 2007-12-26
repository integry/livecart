<?php

include_once(dirname(__file__).'/../../abstract/CreditCardPayment.php');

/**
 *
 * @package library.payment.method.cc
 * @author Integry Systems
 */
class ChronoPayGateway extends CreditCardPayment
{
	private $fields = array();

	private $gateway = 'https://secure.chronopay.com/gateway.cgi';

	const OP_SALE 	= 1;
	const OP_REFUND = 2;
	const OP_RECUR 	= 3;
	const OP_AUTH 	= 4;
	const OP_VOID 	= 5;
	const OP_CAPTURE= 6;
	const OP_RECSTOP= 7;

	public function isCreditable()
	{
		return true;
	}

	public function isCardTypeNeeded()
	{
		return false;
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

	/**
	 *	158 currencies are supported as of 25.12.2007, so we simply assume that all currencies are valid
	 */
	public function getValidCurrency($currency)
	{
		return $currency;
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		$this->addField('opcode', self::OP_AUTH);
		return $this->process();
	}

	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		$this->addField('opcode', self::OP_CAPTURE);
		return $this->process();
	}

	/**
	 *	Credit (a part) of customers payment
	 */
	public function credit()
	{
		$this->addField('opcode', self::OP_REFUND);
		return $this->process('');
	}

	/**
	 *	Void the payment (issue full credit)
	 */
	public function void()
	{
		$this->addField('opcode', self::OP_VOID);
		return $this->process();
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		$this->addField('opcode', self::OP_SALE);
		return $this->process();
	}

	private function initHandler()
	{
		$this->addField('product_id', $this->getConfigValue('productid'));

		$this->addField('amount', $this->details->amount->get());
		$this->addField('currency', $this->details->currency->get());
		$this->addField('card_no', $this->getCardNumber());
		$this->addField('cvv', $this->getCardCode());
		$this->addField('expirem', $this->getExpirationMonth());
		$this->addField('expirey', $this->getExpirationYear());

		// customer information
		$this->addField('ip', $this->details->ipAddress->get());
		$this->addField('email', $this->details->email->get());
		$this->addField('phone', $this->details->phone->get());
		$this->addField('fname', $this->details->firstName->get());
		$this->addField('lname', $this->details->lastName->get());
		$this->addField('cardholder', $this->details->getName());
		$this->addField('street', $this->details->address->get());
		$this->addField('city', $this->details->city->get());
		$this->addField('state', $this->details->state->get());
		$this->addField('zip', $this->details->postalCode->get());
		$this->addField('country', $this->details->country->get());

		$this->addField('show_transaction_id', 1);

		if (strlen($this->fields['state']) != 2)
		{
			$this->fields['state'] = '';
		}
	}

	private function addField($field, $value)
	{
		$this->fields[$field] = $value;
	}

	private function process()
	{
		$this->initHandler();

		if ($this->details->gatewayTransactionID->get())
		{
			$this->addField('transaction', $this->details->gatewayTransactionID->get());
		}

		// calculate operation hash
		$hash = $this->getConfigValue('secret') . $this->fields['opcode'] . $this->getConfigValue('productid');

		switch ($this->fields['opcode'])
		{
			case 1:
			case 4:
				$hash .= $this->fields['fname'] . $this->fields['lname'] . $this->fields['street'] . $this->fields['ip'] . $this->fields['card_no'] . $this->fields['amount'];
			break;

			case 2:
			case 5:
			case 6:
			case 3:
				$hash .= $this->fields['transaction'] . (in_array($this->fields['opcode'], array(2, 3)) ?  $this->fields['amount'] : '');
			break;
		}

		$this->addField('hash', md5($hash));

		set_time_limit(0);

		// construct the fields string to pass with HTTP request
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

		$response = trim(urldecode(curl_exec($ch)));

		if (curl_errno($ch))
		{
			return new TransactionError(curl_error($ch), $this->fields);
		}

		curl_close ($ch);

		$response = explode("\n", $response);

		if ('N|' == substr($response[0], 0, 2))
		{
			return new TransactionError(substr($response[0], 2), $response);
		}

		// get transaction ID
		$transactionID = '';
		foreach ($response as $line)
		{
			if ('T' == substr($line, 0, 2))
			{
				$values = explode('|', $line);
				$transactionID = $values[1];
			}
		}

		// prepare TransactionResult object
		$res = new TransactionResult();
		$res->gatewayTransactionID->set($transactionID);
		$res->amount->set($this->fields['amount']);
		$res->currency->set($this->fields['currency']);
		$res->rawResponse->set($response);

		switch ($this->fields['opcode'])
		{
			case self::OP_SALE:
				$res->setTransactionType(TransactionResult::TYPE_SALE);
			break;

			case self::OP_AUTH:
				$res->setTransactionType(TransactionResult::TYPE_AUTH);
			break;

			case self::OP_CAPTURE:
				$res->setTransactionType(TransactionResult::TYPE_CAPTURE);
			break;

			case self::OP_VOID:
				$res->setTransactionType(TransactionResult::TYPE_VOID);
			break;

			case self::OP_REFUND:
				$res->setTransactionType(TransactionResult::TYPE_REFUND);
			break;

			default:
				throw new PaymentException('Transaction type "' . $this->fields['opcode'] . '" is not implemented');
			break;
		}

		return $res;
	}
}

?>