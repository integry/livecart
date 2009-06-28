<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

/**
 *
 * @package library.payment.method
 * @author Integry Systems
 */
class CyberMUT extends ExternalPayment
{
	public function getUrl()
	{
		$url = '';

		switch ($this->getConfigValue('bank'))
		{
			case 'CyberMUT_CM': $url = 'https://paiement.creditmutuel.fr/test/paiement.cgi'; break;
			case 'CyberMUT_CIC': $url = 'https://ssl.paiement.cic-banques.fr/test/paiement.cgi'; break;
			case 'CyberMUT_OBC': $url = 'https://ssl.paiement.banque-obc.fr/test/paiement.cgi'; break;
		}

		if (!$this->getConfigValue('test'))
		{
			$url = str_replace('/test/', '/', $url);
		}

		return $url;
	}

	public function getPostParams()
	{
		$params = array();

		$params['TPE'] = $this->getConfigValue('TPE');
		$params['date'] = date('d/m/y:H:i:s');
		$params['montant'] = $this->details->amount->get() . $this->details->currency->get();
		$params['reference'] = $this->details->invoiceID->get();
		$params['texte-libre'] = '';
		$params['version'] = '1.2open';
		$params['lgue'] = 'EN';
		$params['societe'] = $this->getConfigValue('societe');

		$params['MAC'] = hash_hmac('sha1', implode('*', $params) . '*', $this->getConfigValue('MAC'));

		$params['url_retour'] = $this->cancelUrl;
		$params['url_retour_err'] = $this->cancelUrl;
		$params['url_retour_ok'] = $this->returnUrl;

		return $params;
	}

	public function isPostRedirect()
	{
		return true;
	}

	public function notify($requestArray)
	{
		$this->saveDebug($requestArray);
		exit;

		// check for secret word
		if ($secretWord = $this->getConfigValue('secretWord'))
		{
			$orderNum = 'Y' == $requestArray['demo'] ? 1 : $requestArray['order_number'];
			$expected = $secretWord . $requestArray['sid'] . $orderNum . $requestArray['total'];
			if ($requestArray['key'] != strtoupper(md5($expected)))
			{
				return new TransactionError('Invalid 2Checkout secret word', $requestArray);
			}
		}

		$result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['order_number']);
		$result->amount->set($requestArray['total']);
		$result->currency->set($this->get2CoCurrency());
		$result->rawResponse->set($requestArray);
		$result->setTransactionType(TransactionResult::TYPE_SALE);

		return $result;
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['merchant_order_id'];
	}

	public function getReturnUrlFromRequest($requestArray)
	{
		return $requestArray['complete_url'];
	}

	public function isHtmlResponse()
	{
		return false;
	}

	public function getValidCurrency($currentCurrencyCode)
	{
		$currentCurrencyCode = strtoupper($currentCurrencyCode);
		return in_array($currentCurrencyCode, array('ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'IDR', '0JPY', 'KES', 'KRW', 'MXP', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'PTE', 'SEK', 'SGD', 'SKK', 'THB', 'TWD', 'USD', 'VND', 'ZAR')) ? $currentCurrencyCode : 'USD';
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