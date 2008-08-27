<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class MoneyBookers extends ExternalPayment
{
	public function getUrl()
	{
		$url = 'https://www.moneybookers.com/app/payment.pl';

		$params = array();
		$params['cmd'] = '_xclick';
		$params['pay_to_email'] = $this->getConfigValue('email');
		$params['recipient_description'] = $this->application->getConfig()->get('STORE_NAME');
		$params['item_name'] = $this->getConfigValue('ITEM_NAME');
		$params['amount'] = $this->details->amount->get();
		$params['currency'] = $this->details->currency->get();
		$params['transaction_id'] = $this->details->invoiceID->get();
		$params['cancel_url'] = $this->returnUrl;
		$params['return_url'] = $this->returnUrl;
		$params['status_url'] = $this->notifyUrl;
		$params['language'] = 'EN';

		// customer information
		$params['firstname'] = $this->details->firstName->get();
		$params['lastname'] = $this->details->lastName->get();
		$params['address'] = $this->details->address->get();
		$params['city'] = $this->details->city->get();
		$params['state'] = $this->details->state->get();
		$params['postal_code'] = $this->details->postalCode->get();
		$params['country'] = $this->details->country->get();
		$params['pay_from_email'] = $this->details->email->get();
		$params['phone_number'] = $this->details->phone->get();

		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . urlencode($value);
		}

		$paymentUrl = $url . '?' . implode('&', $pairs);

		// try to initiate a MoneyBookers session
		if (function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $paymentUrl);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11');
			curl_setopt($ch, CURLOPT_HEADER, true); // header will be at output
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // HTTP request is 'HEAD'

			$content = curl_exec($ch);
			curl_close($ch);

			preg_match('/SESSION_ID\=([a-z0-9]{32})/', $content, $sessionId);

			if (!empty($sessionId[1]))
			{
				$paymentUrl = $url . '?sid=' . $sessionId[1];
			}
		}

		return $paymentUrl;
	}

	public function notify($requestArray)
	{
		$secretWord = $this->getConfigValue('secretWord');
		$md5 = $requestArray['merchant_id'] . $requestArray['transaction_id'] . strtoupper(md5($secretWord)) . $requestArray['mb_amount'] . $requestArray['mb_currency'] . $requestArray['status'];
		file_put_contents('/var/www/livecart/cache/mb-md', $md5);
		$md5 = strtoupper(md5($md5));

		if (in_array($requestArray['status'], array(1, 2)) && (($md5 == $requestArray['md5sig']) || !$secretWord) && ($this->getConfigValue('email') == $requestArray['pay_to_email']))
		{
			$result = new TransactionResult();
			$result->gatewayTransactionID->set($requestArray['mb_transaction_id']);
			$result->amount->set($requestArray['amount']);
			$result->currency->set($requestArray['currency']);
			$result->rawResponse->set($requestArray);
			$result->setTransactionType(TransactionResult::TYPE_SALE);
		}
		else
		{
			$result = new TransactionError('Transaction declined', $requestArray);
		}

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		file_put_contents('/var/www/livecart/cache/mb', var_export($requestArray, true));
		return $requestArray['transaction_id'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return null;
	}

	public function isHtmlResponse()
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
		return array('EUR', 'TWD', 'USD', 'THB', 'GBP', 'CZK', 'HKD', 'HUF', 'SGD', 'SKK', 'JPY', 'EEK', 'CAD', 'BGN', 'AUD', 'PLN', 'CHF', 'ISK', 'DKK', 'INR', 'SEK', 'LVL', 'NOK', 'KRW', 'ILS', 'ZAR', 'MYR', 'RON', 'NZD', 'HRK', 'TRY', 'LTL');
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