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
	

	class gWebListener {
		var $_raw_message;
		var $_message_type;
		var $_message;
		var $_rev_merchant_id;
		var $_rev_merchant_key;
		var $_my_merchant_id;
		var $_my_merchant_key;
		var $_auto_authenticate;
		
		
		/**
		 * Public Constructor
		 *
		 * @param unknown_type $auto_authenticate
		 * @param unknown_type $merchant_id
		 * @param unknown_type $merchant_key
		 * @return gWebListener
		 */
		function gWebListener($merchant_id = null, $merchant_key = null, $auto_authenticate = true) {
			$this->_auto_authenticate = $auto_authenticate;
			$this->_my_merchant_id	  = $merchant_id;
			$this->_my_merchant_key	  = $merchant_key;
		}
		
		
		////////////////////////////////////
		// Public Methods
		////////////////////////////////////
		
		/**
		 * Start The Web Listener
		 *
		 */
		function Start() {
			
			/**
			 * Authorize Message Post
			 */
			$this->_HTTPGetAuthentication();
			
			/**
			 * Auto Authentication
			 */
			if($this->_auto_authenticate) {
				if($this->_my_merchant_id != $this->_rev_merchant_id ||
				   $this->_my_merchant_key != $this->_rev_merchant_key) {
				   exit();
				   }
			}
			
			
			/**
			 * Read Input Stream
			 */
			$this->_getRawPostData();
			$this->_parseRawMessage();
			
		}
		
		
		/**
		 * Returns the received merchant id from the
		 * authentication header
		 *
		 * @return unknown
		 */
		function getHTTPMerchantId() {
			return $this->_rev_merchant_id;
		}
		
		
		/**
		 * Returns the received merchant key from the
		 * authentication header
		 *
		 * @return unknown
		 */
		function getHTTPMerchantKey() {
			return $this->_rev_merchant_key;
		}
		
		/**
		 * Returns Message Type
		 *
		 * @return unknown
		 */
		function getMessageType() {
			return $this->_message_type;
		}
		
		
		/**
		 * Return Message
		 *
		 * @return unknown
		 */
		function getMessage() {
			switch($this->_message_type) {
				
				
				case NEW_ORDER_NOTIFICATION:
					$message = new gNewOrderNotification();
					$message->buildMessage($this->_message);
					return $message;
					
					
				case ORDER_STATE_CHANGE_NOTIFICATION:
					$message = new gOrderStateChangeNotification();
					$message->buildMessage($this->_message);
					return $message;
					
					
				case CHARGE_AMOUNT_NOTIFICATION:
					$message = new gChargeAmountNotification();
					$message->buildMessage($this->_message);
					return $message;
					
					
				case RISK_INFORMATION_NOTIFICATION:
					$message = new gRiskInformationNotification();
					$message->buildMessage($this->_message);
					return $message;
					
					
				case REFUND_AMOUNT_NOTIFICATION:
					$message = new gRefundAmountNotification();
					$message->buildMessage($this->_message);
					return $message;
					
					
				case CHARGEBACK_AMOUNT_NOTIFICATION:
					$message = new gChargebackAmountNotification();
					$message->buildMessage($this->_message);
					return $message;
					
			}
		}
		
		////////////////////////////////////
		// Private Methods
		////////////////////////////////////
		
		/**
		 * Enter description here...
		 *
		 */
		function _HTTPGetAuthentication(){
			/**
			 * Perform HTTP Authentication
			 * Send Headers
			 */
			  if (!isset($_SERVER['PHP_AUTH_USER'])) {
			   	 header('WWW-Authenticate: Basic realm="My Realm"');
			   	 header('HTTP/1.0 401 Unauthorized');
			   	 echo 'Unauthorized\n';
			   	 exit;
			  } else {
			  	/**
			  	 * Get Username and password
			  	 */
			   	$this->_rev_merchant_id = $_SERVER['PHP_AUTH_USER'];
			   	$this->_rev_merchant_key= $_SERVER['PHP_AUTH_PW'];
  			}
		}
		
		/**
		 * Read the raw post data from the input stream
		 *
		 */
		function _getRawPostData() {
			
			/**
			 * 
			 */
			$fp=   fopen("php://input", "r");
			
	
			/**
			 * Read from input
			 */
			$input = '';
			while(!feof($fp)) {
				$input .= fread($fp, sizeof($fp));
			}
			
			/**
			 * Close Stream
			 */
			fclose($fp);
			
			
			/**
			 * Set Received Message
			 */
			$this->_raw_message 	= $input;
		}
		
	
		/**
		 * Parse the Raw Message
		 *
		 */
		function _parseRawMessage() {
			$xml_parser = new XML_Unserializer();
			if($xml_parser->unserialize($this->_raw_message)) {
				$this->_parseMessageType($this->_raw_message);
				$parsed_response = $xml_parser->getUnserializedData();
				$this->_message = $parsed_response;
			}
			else 
				$this->_message = false;
		}
	
	
		/**
		 * Parse Message Type
		 *
		 * @param unknown_type $raw_data
		 */
		function _parseMessageType($raw_data) {		
			/**
			 * New Order Notification
			 */
			if(strstr($raw_data, NEW_ORDER_NOTIFICATION)) {
				$this->_message_type = NEW_ORDER_NOTIFICATION;
			}
			
			/**
			 * Risk Information Notification
			 */
			if(strstr($raw_data, RISK_INFORMATION_NOTIFICATION)) 
				$this->_message_type = RISK_INFORMATION_NOTIFICATION;
				
			/**
			 * Order State Change Notification
			 */
			if(strstr($raw_data, ORDER_STATE_CHANGE_NOTIFICATION)) 
				$this->_message_type	= ORDER_STATE_CHANGE_NOTIFICATION;
				
			/**
			 * Charge Amount Notification
			 */
			if(strstr($raw_data, CHARGE_AMOUNT_NOTIFICATION))
				$this->_message_type	= CHARGE_AMOUNT_NOTIFICATION;
				
			/**
			 * Refund Amount Notification
			 */
			if(strstr($raw_data, REFUND_AMOUNT_NOTIFICATION)) 
				$this->_message_type	= REFUND_AMOUNT_NOTIFICATION;
				
			/**
			 * Charge Back Amount Notification
			 */
			if(strstr($raw_data, CHARGEBACK_AMOUNT_NOTIFICATION))
				$this->_message_type	= CHARGEBACK_AMOUNT_NOTIFICATION;
			
		}
	
	}
?>