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
	
	class gSendBuyerMessage {
		var $_send_email;
		var $_message;
		var $_google_order_number;
		
		
		/**
		 * Constructor
		 *
		 * @param unknown_type $message
		 * @param unknown_type $send_email
		 * @return gSendBuyerMessage
		 */
		function gSendBuyerMessage($google_order_number, $message, $send_email = false) {
			$this->_send_email 	= $send_email;
			$this->_message		= $message;
			$this->_google_order_number = $google_order_number;
		}
		
		
		
		function getXML() {
			$str_xml = "<?xml version=\"1.0\" encoding=\"UTF-8.0\"?>";
			
			$str_xml.= "<send-buyer-message xmlns=\"".$GLOBALS['GCheckout_xmlSchema']."\" google-order-number=\"$this->_google_order_number\">";
			$str_xml .= "<message>$this->_message</message>";
			
			if(!empty($this->_send_email)) 
				$str_xml .= "<send-email>".($this->_send_email == true ? GCHECKOUT_TRUE : GCHECKOUT_FALSE)."</send-email>";
				
				
			$str_xml .= "</send-buyer-message>";
			
			return $str_xml;
		}
	}
?>