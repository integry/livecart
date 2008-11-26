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
	
	class gDeliverOrder {
		var $_google_order_number;
		var	$_carrier;
		var $_tracking_number;
		var $_send_email;
		
		
		/**
		 * Deliver Order
		 *
		 * @param unknown_type $google_order_number
		 * @param unknown_type $carrier
		 * @param unknown_type $tracking_number
		 * @param unknown_type $send_email
		 * @return gDeliverOrder
		 */
		function gDeliverOrder($google_order_number, $carrier = '', $tracking_number ='', $send_email = false) {
			$this->_google_order_number		= $google_order_number;
			$this->_carrier					= $carrier;
			$this->_tracking_number			= $tracking_number;
			$this->_send_email				= $send_email;
		}
		
		
		/**
		 * Return XML Message
		 *
		 * @return unknown
		 */
		function getXML() {
			$str_xml = "<?xml version=\"1.0\" encoding=\"UTF-8.0\"?>";
			$str_xml.= "<deliver-order xmlns=\"".$GLOBALS['GCheckout_xmlSchema']."\" google-order-number=\"$this->_google_order_number\">";

			if(!empty($this->_carrier) && !empty($this->_tracking_number)) 
				$str_xml .= "<tracking-data>
								<carrier>$this->_carrier</carrier>
							 	<tracking-number>$this->_tracking_number</tracking-number>
							 </tracking-data>";
				
			if(!empty($this->_send_email)) 
				$str_xml .= "<send-email>".($this->_send_email = true ? GCHECKOUT_TRUE : GCHECKOUT_FALSE)."</send-email>";
				
				
				
			$str_xml.= "</deliver-order>";
			
			return $str_xml;
		}
	}
?>