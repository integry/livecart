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
	
	
	/**
	 * Returns Markup for Google Checkout Button
	 *
	 */
	class gButton {
		var $_gCart;
		var $_size;
		var $_style;
		var $_variant;
		var $_loc;
		var $_onsubmit;
		
		
		/**
		 * Builds Google Checkout Button Constructor
		 *
		 * @param unknown_type $gCart
		 * @param unknown_type $size
		 * @param unknown_type $style
		 * @param unknown_type $variant
		 * @return gButton
		 */
		function gButton($gCart, $size = "large", $style = "white", $variant = "text") {
			$this->_gCart = $gCart;
			$this->_size = $size;
			$this->_style = $style;
			$this->_variant = $variant;
			$this->_loc 	= "en_US";
		}
		
		
		
		///////////////////////////////////////
		// Public Methods
		///////////////////////////////////////
		
		/**
		 * Returns HTML Form that Posts to cart to Google Checkout
		 *
		 */
		function getPostButton(){
			
			/**
			 * Local Variables
			 */
			$w;
			$h;
			$var	= $this->_variant;
			$style	= $this->_style;
			$loc	= $this->_loc;
			$merchant_id = $this->_gCart->_mercant_id;
			$b64_cart 	 = base64_encode($this->_gCart->getCart());
			$b64_signature= base64_encode($this->_gCart->getSignature($this->_gCart->getCart()));
			$domain		 = $GLOBALS['checkout_domain'];
			$onsubmit	 = $this->_onsubmit;
			
			
			/**
			 * BUTTON PATH DEPENDS ON THE ENVIROMENT
			 */
			if($domain == 'sandbox.google.com'){
				$button_path 	= "checkout/buttons";
				$checkout_path 	= "checkout/api/checkout/v2/checkout/Merchant";
			}
			else {
				$button_path 	= "buttons";
				$checkout_path 	= "api/checkout/v2/checkout/Merchant/";
			}
			
			
			
			/**
			 * Set Dimensions
			 */
			$this->_setDimensions($this->_size, &$w, &$h);
			
			/**
			 * Build HTML String
			 */
			if(strtoupper($this->_variant) == "TEXT") {
				$str_html = "
								<!-- Google Checkout Button Post -->
								<form action=\"https://$domain/$checkout_path/$merchant_id\" method=\"post\" id=\"google_checkout_form\" onsubmit=\"$onsubmit\" >
								<input type=\"hidden\" name=\"cart\" value=\"$b64_cart\" />
								<input type=\"hidden\" name=\"signature\" value=\"$b64_signature\" />
								<input type=\"image\" name=\"Google Checkout\" alt=\"Fast checkout through Google\" 
								src=\"https://$domain/$button_path/checkout.gif?merchant_id=$merchant_id&w=$w&h=$h&style=$style&variant=$var&loc=$loc\"
								height=\"$h\" width=\"$w\" >
								</form>
							";
			}
			else {
				
				
				$str_html = "
								<!-- Google Checkout Disabled Button Image -->
								<input type=\"image\" name=\"Google Checkout\" alt=\"Fast checkout through Google\" 
								src=\"https://$domain/$button_path/checkout.gif?merchant_id=$merchant_id&w=$w&h=$h&style=$style&variant=$var&loc=$loc\"
								height=\"$h\" width=\"$w\" >
							";
			}
			
			return $str_html;
		}
		
		
		////////////////////////////////////////
		// Private Methods
		////////////////////////////////////////
		
		/**
		 * Sets Dimensions of the button
		 *
		 * @param unknown_type $size
		 * @param unknown_type $w
		 * @param unknown_type $h
		 */
		function _setDimensions($size, &$w, &$h) {
			switch (strtoupper($size)) {
				case "LARGE":
					$w = 180;
					$h = 46;
					break;
				case "MEDIUM":
					$w = 168;
					$h = 44;
					break;
				case "SMALL":
					$w = 160;
					$h = 43;
					break;
				default:
					$w = 180;
					$h = 46;
			}
		}

	}
?>