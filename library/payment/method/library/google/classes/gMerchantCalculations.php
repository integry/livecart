<?php

	/**
	 * ========================================================
	 * 	phpGCheckout, Open Source PHP G Checkout Library
	 * 	http://www.phpgcheckout.com
	 * ========================================================
	 * 
	 * Copyright (c) 2006 Expert Database Solutions, LLC
	 * 
	 * Permission is hereby granted, free of charge, to any person obtaining a 
	 * copy of this software and associated documentation files (the "Software"), 
	 * to deal in the Software without restriction, including without limitation the 
	 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
	 * copies of the Software, and to permit persons to whom the Software is 
	 * furnished to do so, subject to the following conditions:
	 * 
	 * The above copyright notice and this permission notice shall be included in all 
	 * copies or substantial portions of the Software.
	 * 
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
	 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
	 * PARTICULAR PURPOSE AND NONINFRINGEMENT. 
	 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR 
	 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT 
	 * OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	 * 
	 */
	
	class gMerchantCalculations {
		var $_merchant_calculations_url;
		var $_accept_merchant_coupons; 	/* Boolean */
		var $_accept_gift_certificates; /* Boolean */
		
		function gMerchantCalculations($url, $bAcceptMerchantCoupons = null, $bAcceptGiftCertificates = null) {
			$this->_merchant_calculations_url 	= $url;
			$this->_accept_merchant_coupons 	= $bAcceptMerchantCoupons;
			$this->_accept_gift_certificates	= $bAcceptGiftCertificates;
		}
		
		
		
		function getXML() {
			$str_xml  = "";
			
			/**
			 * Check for url
			 */
			if(!empty($this->_merchant_calculations_url))
				$str_xml .= "	<merchant-calculations-url>$this->_merchant_calculations_url</merchant-calculations-url>";
			else 
				return "";		/* if there's no url return empty string */
			
				
			/**
			 * Accept Merchant Coupons
			 */
			if(!empty($this->_accept_merchant_coupons))
				$str_xml	.= "		<accept-merchant-coupons>$this->_accept_merchant_coupons</accept-merchant-coupons>";
				
			/**
			 * Accept Gift Certificates
			 */
			if(!empty($this->_accept_gift_certificates))
				$str_xml	.= "		<accept-gift-certificates>$this->_accept_gift_certificates</accept-gift-certificates>";
				

			return $str_xml;
		}
	}
?>