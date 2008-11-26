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
	 * phpGCheckout Globals
	 * 
	 * @author  	Ron Howard 
	 * @copyright   Expert Database Solutions,LLC 2006
	 * 
	 */
	$use_sandbox = true;
	if(defined("PHPGCHECKOUT_USE_SANDBOX")) $use_sandbox = PHPGCHECKOUT_USE_SANDBOX;
	
	
	
	//====================================================================================//
	//	GCheckout Global Variables
	//====================================================================================//

	/**
	 * Set the Global Constants used by the GCheckout Class
	 */
	 $GLOBALS['GCheckout_xmlSchema']		= 'http://checkout.google.com/schema/2';
	 $GLOBALS['GCheckout_currency']      	= 'USD';
	 
	 
	 
	//====================================================================================//
	//	SSL Validation
	//====================================================================================//
	define('VALIDATE_GOOGLE_SSL_CERT', 2);	/*
												1 to check the existence of a common name in the SSL peer certificate. 
												2 to check the existence of a common name and also verify that it matches the hostname provided.
											*/
	
	define('VALIDATE_MY_SSL_CERT', false);
	
	 
	//====================================================================================//
	//	Setup Enviroment
	//====================================================================================//
	
	/**
	 * Decides which include path delimerter to use. Windows should be using
	 * a semi-colon and everything else should be using a colon.
	 */
	if(strpos(__FILE__, ':') !== false) {
		$path_delimiter = ';';
	}
	else {
		$path_delimiter = ':';
	}
	
	/**
	 * Set local installation of required PEAR 
	 * libraries
	 */
	ini_set('include_path', ini_get('include_path').$path_delimiter.dirname(__FILE__).'/PEAR');
	
	
	//====================================================================================//
	//	Link Libraries & Classes
	//====================================================================================//
	require_once('XML/Serializer.php');
	require_once('XML/Unserializer.php');
	require_once('classes/gCart.php');
	require_once('classes/gTaxTable.php');
	require_once('classes/gTaxRule.php');
	require_once('classes/gShipping.php');
	require_once('classes/gItem.php');
	require_once('classes/gMerchantCalculations.php');
	require_once('classes/gMessageRedirect.php');
	require_once('classes/gButton.php');
	require_once('classes/gWebListener.php');
	require_once('classes/gWebPoster.php');
	require_once('classes/notifications/gChargeAmountNotification.php');
	require_once('classes/notifications/gChargebackAmountNotification.php');
	require_once('classes/notifications/gNewOrderNotification.php');
	require_once('classes/notifications/gOrderStateChangeNotification.php');
	require_once('classes/notifications/gRefundAmountNotification.php');
	require_once('classes/notifications/gRiskInformationNotification.php');
	require_once('classes/notifications/gNotificationAcknowledgment.php');
	require_once('classes/commands/gChargeOrder.php');
	require_once('classes/commands/gDeliverOrder.php');
	require_once('classes/commands/gSendBuyerMessage.php');
	require_once('classes/commands/gProcessOrder.php');
	
	
	
	 
	//====================================================================================//
	//	Globals you probably shouldn't change.
	//====================================================================================//
	 
	/**
	 * Google Provided URLs
	 */
	
	if($use_sandbox) 
		$sub_domain = 'sandbox';
	else 
		$sub_domain = 'checkout';
		
	 /**
	  * Google Checkout Button Params
	  */
	 $GLOBALS['GCheckout_button']			= "http://$sub_domain.google.com/buttons/checkout.gif";
	 $GLOBALS['GCheckout_button_w']			= 180;
	 $GLOBALS['GCheckout_button_h']			= 46;
	 $GLOBALS['GCheckout_button_loc']		= 'en_US';
	 $GLOBALS['GCheckout_button_style']		= 'trans';

	 if(!empty($merchant_id)) {
	 	$GLOBALS['merchant_id'] 			= $merchant_id;
	 }
	 else if(!empty($GLOBALS['merchant_id']))
	 	$GLOBALS['merchant_id']				= $GLOBALS['merchant_id'];
	 else 	
	 	$GLOBALS['merchant_id']				= '';
	
	 $base_url = "https://$sub_domain.google.com/cws/v2/Merchant/" .  $GLOBALS["merchant_id"];
	 $GLOBALS["checkout_domain"] = "$sub_domain.google.com";
     $GLOBALS["checkout_url"] = $base_url . "/checkout";
     $GLOBALS["checkout_diagnose_url"] = $base_url . "/checkout/diagnose";
     $GLOBALS["request_url"] = $base_url . "/request";
     $GLOBALS["request_diagnose_url"] = $base_url . "/request/diagnose";
     
     
    //====================================================================================//
	//	Important Enumerations
	//====================================================================================//
	
	/**
	 * Country Areas
	 */
	 define('COUNTRY_AREA_CONTINENTAL_48', 'CONTINENTAL_48');
	 define('COUNTRY_AREA_FULL_50', 'FULL_50_STATES');
	 define('COUNTRY_AREA_ALL', 'ALL');
	 
	 
	 /**
	  * Tax Table Types
	  */
	 define('TAX_TABLE_DEFAULT', 'default');
	 define('TAX_TABLE_ALTERNATE', 'alternate');
	 define('TAX_TABLE_STANDALONE', 'true');
	 
     /**
	 * Declare Shipping Enumerations
	 *
	 */
	define('SHIPPING_FLAT_RATE', 'flat-rate-shipping');
	define('SHIPPING_PICKUP', 'pickup');
	define('SHIPPING_MERCHANT_CALCULATED', 'merchant-calculated-shipping');
	
	
	/**
	 * Google Booleans
	 */
	define('GCHECKOUT_TRUE', 'true');
	define('GCHECKOUT_FALSE', 'false');
	
	
	/**
	 * Google Notification Types
	 */
	define("NEW_ORDER_NOTIFICATION", 'new-order-notification');
	define("RISK_INFORMATION_NOTIFICATION", "risk-information-notification");
	define("ORDER_STATE_CHANGE_NOTIFICATION", "order-state-change-notification");
	define("CHARGE_AMOUNT_NOTIFICATION", "charge-amount-notification");
	define("REFUND_AMOUNT_NOTIFICATION", "refund-amount-notification");
	define("CHARGEBACK_AMOUNT_NOTIFICATION", "chargeback-amount-notification");
	
	
	/**
	 * Google Digital Delivery
	 */
	define("DIGITAL_EMAIL_DELIVERY", 	"digital_email_delivery");
	define("DIGITAL_KEY_URL_DELIVERY", 	"digital_key_url_delivery");
	
	/**
	 * Platform Identifier
	 */
	define("PLATFORM_ID", '459782357066897');
	
?>