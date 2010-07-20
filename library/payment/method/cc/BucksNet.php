<?php

include_once(dirname(__file__).'/../../abstract/CreditCardPayment.php');

/**
 *
 * @package library.payment.method.cc
 * @author Integry Systems
 */
class BucksNet extends CreditCardPayment
{
	private $fields = array();

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
		return false;
	}

	public function getValidCurrency($currency)
	{
		return 'GBO';
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		return $this->process('PreAuth');
	}

	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->process('Sale');
	}

	/**
	 *	Credit (a part) of customers payment
	 */
	public function credit()
	{
		return $this->process('Refund');
	}

	/**
	 *	Void the payment (issue full credit)
	 */
	public function void()
	{
		return $this->process('Refund');
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->process('Sale');
	}

	public function process($type)
	{
		set_time_limit(0);

		$soap = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
					   xmlns:xsd="http://www.w3.org/2001/XMLSchema"
					   xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
		  <soap:Body>
			<Authorise xmlns="http://secure.bucks.net/SecureGateway/">
				<req>
					<Amount>' . $this->details->amount->get() . '</Amount>
					<Card>
					  <CSC>' . $this->getCardCode() . '</CSC>
					  <ExpMonth>' . $this->getExpirationMonth() . '</ExpMonth>
					  <ExpYear>' . $this->getExpirationYear() . '</ExpYear>
					  <IssueNum></IssueNum>
					  <Number>' . $this->getCardNumber() . '</Number>
					  <StartMonth></StartMonth>
					  <StartYear></StartYear>
					  <Type>Unspecified</Type>
					</Card>
					<CustomerID>' . $this->details->clientID->get() . '</CustomerID>
					<Description></Description>
					<HouseNum></HouseNum>
					<OrderID>' . $this->details->invoiceID->get() . '</OrderID>
					<Password>' . $this->getConfigValue('password') . '</Password>
					<Postcode></Postcode>
					<TraderID>' . $this->getConfigValue('traderID') . '</TraderID>
					<TransType>' . $type . '</TransType>
					<Username>' . $this->getConfigValue('username') . '</Username>
					<VatID>' . $this->getConfigValue('vatID') . '</VatID>
				</req>
				<testMode>' . ($this->getConfigValue('test') ? 'true' : 'false') . '</testMode>
			</Authorise>
		  </soap:Body>
		</soap:Envelope>';
// PreAuth or Sale or Refund

		$res = $this->postUrl('https://secure.bucks.net/SecureGateway/SecureGateway.asmx', $soap, 'http://secure.bucks.net/SecureGateway/Authorise');
		$res = substr($res, strpos($res, '<?xml'));
		$res = str_replace('soap:', '', $res);
		$xml = simplexml_load_string($res);

		$xml = $xml->Body->AuthoriseResponse->AuthoriseResult;

		if ('true' == $xml->Success)
		{
			$res = new TransactionResult();
			$res->gatewayTransactionID->set($xml->OpasID);
			$res->amount->set($this->details->amount->get());
			$res->currency->set('GBP');
			$res->rawResponse->set($xml);
		}
		else
		{
			return new TransactionError($xml->StatusText, $xml);
		}

		if ($this->details->gatewayTransactionID->get())
		{
			//$this->addField('x_trans_id', $this->details->gatewayTransactionID->get());
		}

		switch ($type)
		{
			case 'Sale':
				$res->setTransactionType(TransactionResult::TYPE_SALE);
			break;

			case 'PreAuth':
				$res->setTransactionType(TransactionResult::TYPE_AUTH);
			break;

			case 'Refund':
				$res->setTransactionType(TransactionResult::TYPE_REFUND);
			break;

			default:
				throw new PaymentException('Transaction type "' . $type . '" is not implemented');
			break;
		}

		return $res;
	}

	function postUrl($url, $data, $soap_action = 'https://famos-ps.bmw.com/FAMOS/services/StatusService', $timeout = 200)
	{
		$headers = array(
				"POST /SecureGateway/SecureGateway.asmx HTTP/1.0",
				"Content-type: text/xml;charset=\"utf-8\"",
				"Accept: text/xml",
				"SOAPAction: \"$soap_action\"",
				"Content-length: ".strlen($data),
			);

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, ( int ) $timeout );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt($ch, CURLOPT_HEADER, 1);

		$xmlResponse = curl_exec ( $ch );

		$ch_info=curl_getinfo($ch);

		curl_close($ch);

		return $xmlResponse;
	}
}

?>