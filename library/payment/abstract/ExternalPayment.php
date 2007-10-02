<?php

include_once(dirname(__file__) . '/../TransactionPayment.php');

/**
 *
 * @package library.payment.abstract
 * @author Integry Systems 
 */
abstract class ExternalPayment extends TransactionPayment
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

	public function setNotifyUrl($url)
	{
        $this->notifyUrl = $url;
    }

	public function setReturnUrl($url)
	{
        $this->returnUrl = $url;
    }

	public function setSiteUrl($url)
	{
        $this->siteUrl = $url;
    }
}

?>