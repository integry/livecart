<?php

include_once(dirname(__file__) . '/../../abstract/ExpressPayment.php');
include_once(dirname(__file__) . '/../../method/library/paypal/PaypalCommon.php');

/**
 *
 * @package library.payment.method.express
 * @author Integry Systems
 */
class GoogleCheckout extends ExpressPayment
{
	protected $data;

	public function getInitUrl($returnUrl, $cancelUrl, $sale = true)
	{
		$sandbox = $this->getConfigValue('sandbox') ? 'sandbox.' : '';
		$handler = $this->getHandler($returnUrl, $cancelUrl);

		// add cart items
		foreach ($this->order->getOrderedItems() as $item)
		{
			if (!$item->isSavedForLater->get())
			{
				$gItem = new gItem($item->product->get()->getValueByLang('name'), $item->product->get()->getValueByLang('shortDescription'), $item->count->get(), $item->price->get());

				// set sku

				$handler->addItems(array($gItem));
			}
		}

		// add tax rates

		// add discounts

		$url = $sandbox ? 'https://sandbox.google.com/checkout/api/checkout/v2/merchantCheckout/Merchant/':
						  'https://checkout.google.com/api/checkout/v2/merchantCheckout/Merchant/';
		$parsed = new XML_Unserializer();
		if (($response = $handler->_getCurlResponse($handler->getCart(), $url . $this->getConfigValue('merchant_id'))) && ($parsed->unserialize($response)))
		{
			$array = $parsed->getUnserializedData();
			return $array['redirect-url'];
		}
		else
		{
			return false;
		}
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function getTransactionDetails($request = null)
	{
		$paypal = $this->getHandler('GetExpressCheckoutDetails');
		$paypal->setParams($request ? $request['token'] : $this->data['token']);
		$paypal->execute();

		$this->checkErrors($paypal);

		$response = $paypal->getAPIResponse();
		$info = $response->GetExpressCheckoutDetailsResponseDetails->PayerInfo;

		$valueMap = array(

				'firstName' => $info->PayerName->FirstName,
				'lastName' => $info->PayerName->LastName,
				'companyName' => isset($info->PayerBusiness) ? $info->PayerBusiness : '',

				'address' => $info->Address->Street1 . (!empty($info->Address->Street2) ? ', ' . $info->Address->Street2 : ''),
				'city' => $info->Address->CityName,
				'state' => $info->Address->StateOrProvince,
				'country' => $info->Address->Country,
				'postalCode' => $info->Address->PostalCode,

				'email' => $info->Payer,

				// customer data
				'clientID' => $info->PayerID,
			);

		foreach ($valueMap as $key => $value)
		{
			$this->details->$key->set($value);
		}

		return $this->details;
	}

	private function checkErrors($paypal)
	{
		if ($paypal->success())
		{
			$response = $paypal->getAPIResponse();
			if (isset($response->Errors))
			{
				throw new PaymentException($response->Errors->LongMessage);
			}
		}
		else
		{
			throw new PaymentException($paypal->getAPIException()->getMessage());
		}
	}

	public function isCreditable()
	{
		return false;
	}

	public function isVoidable()
	{
		return true;
	}

	public function isMultiCapture()
	{
		return true;
	}

	public function isCapturedVoidable()
	{
		return false;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, self::getSupportedCurrencies()) ? $currentCurrencyCode : 'USD';
	}

	public static function getSupportedCurrencies()
	{
		return array('CAD', 'EUR', 'GBP', 'USD', 'JPY', 'AUD');
	}

	/**
	 *	Reserve funds on customers credit card
	 */
	public function authorize()
	{
		return $this->processAuth('DoExpressCheckoutPayment', 'Order');
	}

	/**
	 *	Capture reserved funds
	 */
	public function capture()
	{
		return $this->processCapture();
	}

	/**
	 *	Void the payment (issue full credit)
	 */
	public function void()
	{
		return $this->processVoid();
	}

	/**
	 *	Authorize and capture funds within one transaction
	 */
	public function authorizeAndCapture()
	{
		return $this->processAuth('DoExpressCheckoutPayment', 'Sale');
	}

	protected function processAuth($api, $type)
	{
		$paypal = $this->getHandler($api);

		$address = PayPalTypes::AddressType(

						$this->details->firstName->get() . ' ' . $this->details->lastName->get(),
						$this->details->address->get(), '' /* address 2 */,
						$this->details->city->get(), $this->details->state->get(),
						$this->details->postalCode->get(), $this->details->country->get(),
						$this->details->phone->get()

					);

		$personName = PayPalTypes::PersonNameType('', $this->details->firstName->get(), '', $this->details->lastName->get());

		$payerInfo = PayPalTypes::PayerInfoType($this->details->email->get(), $this->details->clientID->get(), 'verified', $personName, $this->details->country->get(), '', $address);

		$paymentDetails = PayPalTypes::PaymentDetailsType($this->details->amount->get(), $this->details->amount->get(), 0, 0, 0, $this->details->description->get(), $this->details->clientID->get(), $this->details->invoiceID->get(), '', 'ipn_notify.php', '', array(), $this->details->currency->get());

		$paypal->setParams($type, $this->data['token'], $this->data['PayerID'], $paymentDetails);

		$paypal->execute($api);

		if ($paypal->success())
		{
			$response = $paypal->getAPIResponse();

			if (isset($response->Errors))
			{
				$error = isset($response->Errors->LongMessage) ? $response->Errors : $error = $response->Errors[0];

				return new TransactionError($error->LongMessage, $response);
			}
			else
			{
				$paymentInfo = $response->DoExpressCheckoutPaymentResponseDetails->PaymentInfo;

				$result = new TransactionResult();

				$result->gatewayTransactionID->set($paymentInfo->TransactionID);
				$result->amount->set($paymentInfo->GrossAmount);
				$result->currency->set($response->Currency);

				$result->rawResponse->set($response);

				if ('Sale' == $type)
				{
					$result->setTransactionType(TransactionResult::TYPE_SALE);
				}
				else
				{
					$result->setTransactionType(TransactionResult::TYPE_AUTH);
				}

				return $result;
			}
		}
		else
		{
			return $paypal->getAPIException();
		}
	}

	protected function processCapture()
	{
		return PaypalCommon::processCapture($this->details, $this->getHandler('DoCapture'));
	}

	protected function processVoid()
	{
		return PaypalCommon::processVoid($this);
	}

	public function getHandler($returnUrl = '', $cancelUrl = '')
	{
		$GLOBALS['merchant_id'] = $this->getConfigValue('merchant_id');
		if ($this->getConfigValue('sandbox'))
		{
			define('PHPGCHECKOUT_USE_SANDBOX', true);
		}

		include_once dirname(dirname(__file__)) . '/library/google/config.php';

		$handler = new gCart($this->getConfigValue('merchant_id'), $this->getConfigValue('merchant_key'));
		$handler->setMerchantCheckoutFlowSupport($returnUrl, $cancelUrl, $this->application->getConfig()->get('REQUIRE_PHONE'));

		return $handler;

		//return PaypalCommon::getHandler($this, $api);
	}
}

?>