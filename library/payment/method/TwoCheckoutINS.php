<?php

include_once(dirname(__file__) . '/TwoCheckout.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class TwoCheckoutINS extends TwoCheckout
{
	public function notify($requestArray)
	{
		$this->saveDebug($requestArray);

		if ('ORDER_CREATED' != $requestArray['message_type'])
		{
			return new TransactionError('Invalid 2CO message type', $requestArray);;
		}

		// check for secret word
		if (0 && ($secretWord = $this->getConfigValue('secretWord')))
		{
			$expected = $requestArray['sale_id'] . $requestArray['vendor_id'] . $requestArray['invoice_id'] . $secretWord;
			if ($requestArray['md5_hash'] != strtoupper(md5($expected)))
			{
				return new TransactionError('Invalid 2Checkout secret word', $requestArray);
			}
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['sale_id']);
		$result->amount->set($requestArray['invoice_list_amount']);
		$result->currency->set($requestArray['list_currency']);
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['vendor_order_id'];
	}

	public function isHtmlResponse()
	{
		return true;
	}
}

?>
