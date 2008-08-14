<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class iDEAL extends ExternalPayment
{
	public function getUrl()
	{
		return 'https://www.vcs.co.za/vvonline/ccform.asp';
	}

	public function isPostRedirect()
	{
		return true;
	}

	public function getPostParams()
	{
		$params = array();

		// order info
		$params['PSPID'] = $this->getConfigValue('account');
		$params['orderID'] = $this->details->invoiceID->get();
		$params['COM'] = $this->getConfigValue('description');
		$params['amount'] = $this->details->amount->get();
		$params['currency'] = 'EUR';
		$params['language'] = 'NL_NL';
		$params['PM'] = 'iDEAL';

		// customer information
		$params['CN'] = $this->details->getName();
		$params['owneraddress'] = $this->details->address->get();
		$params['ownertown'] = $this->details->city->get();
		$params['ownerzip'] = $this->details->postalCode->get();
		$params['ownercty'] = $this->details->country->get();
		$params['EMAIL'] = $this->details->email->get();

		// needed?
		$params['submit1'] = 'submit';

		return $params;
	}

	public function notify($requestArray)
	{
		echo '<CallBackResponse>Accepted</CallBackResponse>';

file_put_contents('/var/www/livecart/cache/vcs', var_export($requestArray, true));

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['p2']);
		$result->amount->set($requestArray['p6']);
		$result->currency->set('ZAR');
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['p2'];
	}

	public function isHtmlResponse()
	{
		return true;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return 'ZAR';
	}

	public function isVoidable()
	{
		return false;
	}

	public function void()
	{
		return false;
	}
}

?>