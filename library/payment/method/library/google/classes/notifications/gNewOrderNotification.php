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
	 * New Order Notification
	 *
	 */
	class gNewOrderNotification {
		var $timestamp;
		var $google_order_number;
		var $shopping_cart;
		var $buyer_shipping_address;
		var $buyer_billing_address;
		var $buyer_marketing_preferences;
		var $order_adjustment;
		var $order_total;
		var $fulfillment_order_state;
		var $financial_order_state;
		var $buyer_id;
		
		
		/**
		 * buildMessage
		 *
		 * @param unknown_type $arr_message
		 * @return gNewOrderNotification
		 */
		function buildMessage($arr_message) {
			$this->timestamp 					= $arr_message['timestamp'];
			$this->google_order_number 			= $arr_message['google-order-number'];
			$this->shopping_cart				= $arr_message['shopping-cart'];
			$this->buyer_shipping_address		= $arr_message['buyer-shipping-address'];
			$this->buyer_billing_address		= $arr_message['buyer-billing-address'];
			$this->buyer_marketing_preferences	= $arr_message['buyer-marketing-preferences'];
			$this->order_adjustment				= $arr_message['order-adjustment'];
			$this->order_total					= $arr_message['order-total'];
			$this->fulfillment_order_state		= $arr_message['fulfillment-order-state'];
			$this->financial_order_state		= $arr_message['financial-order-state'];
			$this->buyer_id						= $arr_message['buyer-id'];
		}
	}
?>