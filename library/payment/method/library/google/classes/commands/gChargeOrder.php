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
	

	class gChargeOrder {
		var $_google_order_number;
		var $_amount;
		
		
		/**
		 * Public Constructor
		 *
		 * @param unknown_type $google_order_number
		 * @param unknown_type $amount
		 * @return gChargeOrder
		 */
		function gChargeOrder($google_order_number, $amount = null) {
			$this->_google_order_number = $google_order_number;
			$this->_amount				= $amount;
		}
		
		
		
		/**
		 * Return Charge Order XML
		 *
		 * @return unknown
		 */
		function getXML() {
			$str_xml = "<?xml version=\"1.0\" encoding=\"UTF-8.0\"?>";
			$str_xml.= "<charge-order xmlns=\"".$GLOBALS['GCheckout_xmlSchema']."\" google-order-number=\"$this->_google_order_number\">";
			
			if(!empty($this->_amount)){
				$str_xml.= "<amount currency=\"".$GLOBALS['GCheckout_currency']."\">$this->_amount</amount>";
			}
			
			
			$str_xml.= "</charge-order>";
			
			return $str_xml;
		}
	}
?>