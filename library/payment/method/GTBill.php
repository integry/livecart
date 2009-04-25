<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class GTBill extends ExternalPayment
{
	public function getUrl()
	{
		return 'https://sale.GTBill.com/quickpay.aspx';
	}

	public function getPostParams()
	{
		$params = array();

		$params['MerchantID'] = $this->getConfigValue('MerchantID');
		$params['SiteID'] = $this->getConfigValue('SiteID');

		// a unique order id from your program. (128 characters max)
		$params['MerchantReference'] = $this->details->invoiceID->get();

		// the total amount to be billed, in decimal form, without a currency symbol.
		$params['AmountTotal'] = $this->details->amount->get();
		$params['CurrencyID'] = $this->details->currency->get();

		$params['ConfirmURL'] = $this->notifyUrl;
		$params['ReturnURL'] = $this->returnUrl;

		// customer information
		$params['FirstName'] = $this->details->firstName->get();
		$params['LastName'] = $this->details->lastName->get();
		$params['Address1'] = $this->details->address->get();
		$params['City'] = $this->details->city->get();
		$params['State'] = $this->details->state->get();
		$params['PostalCode'] = $this->details->postalCode->get();
		$params['Country'] = $this->details->country->get();
		$params['Email'] = $this->details->email->get();
		$params['PhoneNumber'] = $this->details->phone->get();

		$items = array(array('Price', 'Qty', 'Code', 'Description', 'Flags'));
		$index = -1;
		foreach ($this->details->getLineItems() as $item)
		{
			if ($item['sku'] != 'shipping')
			{
				$suffix = '[' . ++$index . ']';
				$params['ItemAmount' . $suffix] = $item['price'] * $item['quantity'];
				$params['ItemQuantity' . $suffix] = $item['quantity'];
				$params['ItemDesc' . $suffix] = $item['name'];
				$params['ItemName' . $suffix] = $item['sku'];
			}
			else
			{
				if (!isset($params['AmountShipping']))
				{
					$params['AmountShipping'] = 0;
				}

				$params['AmountShipping'] += $item['price'];
			}
		}

		return $params;
	}

	public function notify($requestArray)
	{
		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['TransactionID']);
		$result->amount->set($requestArray['Amount']);
		$result->currency->set('USD');
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['MerchantReference'];
	}

	public function isHtmlResponse()
	{
		return false;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		return $currentCurrencyCode;
	}

	public function isPostRedirect()
	{
		return true;
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