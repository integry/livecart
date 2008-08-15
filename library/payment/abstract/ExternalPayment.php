<?php

include_once(dirname(__file__) . '/OnlinePayment.php');

/**
 *
 * @package library.payment.abstract
 * @author Integry Systems
 */
abstract class ExternalPayment extends OnlinePayment
{
	/**
	 *	Return payment page URL
	 */
	abstract public function getUrl();

	/**
	 *	Payment confirmation post-back
	 */
	abstract public function notify($requestArray);

	/**
	 *	Extract order ID from payment gateway response data
	 */
	abstract public function getOrderIdFromRequest($requestArray);

	/**
	 *	Determine if HTML output is required as post-notification response
	 *  @return bool
	 */
	abstract public function isHtmlResponse();

	public function isMultiCapture()
	{
		return false;
	}

	public function isCapturedVoidable()
	{
		return false;
	}

	public function setNotifyUrl($url)
	{
		$this->notifyUrl = $url;
	}

	public function setReturnUrl($url)
	{
		$this->returnUrl = $url;
	}

	public function setCancelUrl($url)
	{
		$this->cancelUrl = $url;
	}

	public function setSiteUrl($url)
	{
		$this->siteUrl = $url;
	}

	/**
	 * Determines if the user redirect is done by form submission (POST)
	 * or if it can be done by redirecting using a direct URL (GET)
	 */
	public function isPostRedirect()
	{
		return false;
	}

	/**
	 * Determines if the transaction information is posted back by payment gateway
	 */
	public function isNotify()
	{
		return true;
	}

	/**
	 * Key => value parameter array to be used in POST based redirects
	 */
	public function getPostParams()
	{
		return array();
	}
}

?>