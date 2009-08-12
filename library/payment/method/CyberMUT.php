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

		$fields = implode('*', $params) . '*';
		$params['MAC'] = $this->CMCIC_hmac($fields);

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
		$requestArray['version'] = '1.2open';
		$keys = array('TPE', 'date', 'montant', 'reference', 'texte-libre', 'version', 'code-retour');
		$values = array();
		foreach (array('TPE', 'date', 'montant', 'reference', 'texte-libre', 'version', 'code-retour') as $key)
		{
			$values[$key] = $requestArray[$key];
		}

		$macParams = array_combine($keys, $values);
		$mac = $requestArray['retourPLUS'] . implode('+', $macParams) . '+';
		$hash = strtoupper($this->CMCIC_hmac($mac));

		ob_end_clean();

		if ($hash == $requestArray['MAC'])
		{
			$result = new TransactionResult();
			$result->gatewayTransactionID->set($requestArray['reference']);
			$result->amount->set(substr($requestArray['montant'], 0, -3));
			$result->currency->set(substr($requestArray['montant'], -3));
			$result->rawResponse->set($requestArray);
			$result->setTransactionType(TransactionResult::TYPE_SALE);

			printf("Pragma: no-cache \nContent-type: text/plain \nVersion: 1 %s", 'OK');

			return $result;
		}
		else
		{
			printf("Pragma: no-cache \nContent-type: text/plain \nVersion: 1 %s", 'Document falsifie');
			exit;
		}
	}

	public function getOrderIdFromRequest($requestArray)
	{
		return $requestArray['reference'];
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

	private function CMCIC_hmac($data="")
	{
		$k1 = pack("H*", sha1($this->getConfigValue('key1')));
		$l1 = strlen($k1);

		$k2 = pack("H*", $this->getConfigValue('key2'));
		$l2 = strlen($k2);
		if ($l1 > $l2):
			$k2 = str_pad($k2, $l1, chr(0x00));
		elseif ($l2 > $l1):
			$k1 = str_pad($k1, $l2, chr(0x00));
		endif;

		if ($data==""):
			$d = "CtlHmac"."1.2open".$this->getConfigValue('TPE');
		else:
			$d = $data;
		endif;

		return strtolower($this->hmac_sha1($k1 ^ $k2, $d));
	}

	private function hmac_sha1($key, $data)
	{
		$length = 64; // block length for SHA1
		if (strlen($key) > $length) { $key = pack("H*",sha1($key)); }
		$key  = str_pad($key, $length, chr(0x00));
		$ipad = str_pad('', $length, chr(0x36));
		$opad = str_pad('', $length, chr(0x5c));
		$k_ipad = $key ^ $ipad ;
		$k_opad = $key ^ $opad;

		return sha1($k_opad  . pack("H*",sha1($k_ipad . $data)));
	}
}

?>