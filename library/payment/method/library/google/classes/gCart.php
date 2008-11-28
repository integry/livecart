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
	 * Google GCheckout / Checkout Shopping cart object
	 *
	 * @author  Ron Howard
	 * @copyright  Expert Database Solutions, LLC 2006
	 *
	 */
	class gCart{
		var $_php_version;
		var $_mercant_id;
		var $_mercant_key;
		var $_arr_shopping_cart;
		var $_serializer_options;
		var $_state_serializer_options;
		var $_zip_serializer_options;
		var $_state_area_serializer_options;
		var $_alt_tax_table_serializer_options;
		var $_remove_tags;
		var $_tax_tables;
		var $_shipping;
		var $_items;
		var $_merchant_private_data;
		var $_merchant_calculations;
		var $_platform_id;


		/**
		 * phpGCheckout Constructor
		 *
		 * @param  string mercant_id
		 * @param string $mercant_key
		 */
		function gCart($mercant_id, $mercant_key, $cart_expires = '', $merchant_private_data= "") {

			/**
			 * Set your Google GCheckout Mercant information.
			 */
			$this->_mercant_id 	= $mercant_id;
			$this->_mercant_key = $mercant_key;


			/**
			 * Set Current PHP Versionin information.
			 */
			$this->_php_version = explode("-", phpversion());
			$this->_php_version = explode(".", $this->_php_version[0]);


			/**
			 * Initialize Shopping Cart
			 */
			$this->_setShoppingCart();

			/**
			 * Set Serializer Options
			 */
			$this->_setSerializerOptions();


			/**
			 * Check Cart Expires Date
			 */
			if(!empty($cart_expires)) {
				$this->setCartExpirationDate($cart_expires);
			}

			/**
			 * Merchant Private Data
			 */
			$this->_merchant_private_data = $merchant_private_data;


			/**
			 * set remove tags
			 */
			$this->_remove_tags = array("<REMOVE>", "</REMOVE>");


			/**
			 * Add Platform ID
			 */
			if(defined('PLATFORM_ID'))
				$this->_platform_id = PLATFORM_ID;
		}


		//////////////////////////////////////////////
		// PUBLIC  METHODS
		//////////////////////////////////////////////

		/**
		 * Returns the XML GCheckout Shopping Cart
		 *
		 * @return  XML  GCheckout Checkout Shopping Cart
		 * @access public
		 */
		function getCart() {


				/**
				 * Add Tax Tables to the cart
				 */
				if(!empty($this->_tax_tables))
					$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['tax-tables'] = $this->_tax_tables;

				/**
				 * Add Shipping Methods to the cart
				 */
				if(!empty($this->_shipping))
					$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods'] = $this->_shipping;


				/**
				 * Add Items to the cart
				 */
				if(!empty($this->_items))
					$this->_arr_shopping_cart['shopping-cart']['items'] = $this->_items;


				/**
				 * Add Merchant Private Data to cart
				 */
				if(!empty($this->_merchant_private_data))
					$this->_arr_shopping_cart['shopping-cart']['merchant-private-data'] = $this->_merchant_private_data;

				/**
				 * Add Merchant Calculations to cart
				 */
				if(!empty($this->_merchant_calculations))
					$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['merchant-calculations'] = $this->_merchant_calculations;



				/**
				 * Add Platform ID
				 */
				if(!empty($this->_platform_id))
					$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['platform-id'] = $this->_platform_id;

				/**
				 * Get New XML Serializer
				 */
				$serializer = new XML_Serializer($this->_serializer_options);
				$rslt = $serializer->serialize($this->_arr_shopping_cart);



				// Display the XML document;
				$data =  $serializer->getSerializedData();

				$data = str_replace('<tax-tables>', '<tax-tables merchant-calculated="true">', $data);

				return $data;
		}





		/**
		 * Returns the XML Shopping Cart Signature
		 *
		 * @param string $xml_cart
		 * @return  string
		 * @access public
		 */
		function getSignature($xml_cart) {
			return $this->_getHmacSha1($xml_cart, $this->_mercant_key);
		}




		/**
		 * Adds an Item to the GCheckout Cart
		 *
		 * @param unknown_type $item_name
		 * @param unknown_type $item_description
		 * @param unknown_type $quantity
		 * @param unknown_type $unit_price
		 * @param unknown_type $tt_selector
		 * @param unknown_type $private_item_data
		 * @access public
		 */
		function addItem($item_name, $item_description, $quantity = 1, $unit_price = 0,
						 $tt_selector="", $private_item_data="") {

			 	/**
			 	 * Check if there are already items in the cart
			 	 */
			 	if(empty($this->_arr_shopping_cart['shopping-cart']['items'])) {
			 		$this->_arr_shopping_cart['shopping-cart']['items']	= array();
			 	}

				/**
				 * Strip HTML entities
				 */
				$item_name 			= htmlentities($item_name);
				$item_description 	= htmlentities($item_description);


				/**
				 * Build New Item Array
				 */
				$arr_item =  array(
										'item-name' => $item_name,
										'item-description' => $item_description,
										'unit-price' => array(
																'_attributes' => array('currency' => $GLOBALS['GCheckout_currency']),
																'_content' 	  => $unit_price
															 ),
										'quantity' => $quantity

								);

				if(!empty($private_item_data)) {
					$arr_item['merchant-privat-item-data'] = $private_item_data;

				}

				if(!empty($tt_selector)) {
					$arr_item['tax-table-selector'] = $tt_selector;
				}


				/**
				 * Push the Item into the cart
				 */
				array_push($this->_arr_shopping_cart['shopping-cart']['items'], $arr_item);

		}



		/**
		 * Add an array of gItem objects to your cart
		 *
		 *
		 * @param array $arr_items
		 * @access public
		 */
		function addItems($arr_items) {
			$str_xml = "";
			foreach ($arr_items as $item) {
				$str_xml .= $item->getXML();
			}


			$this->_items = $str_xml;

		}




		/**
		 * Sets the expiration of the shopping cart. Note: google specified UTC time.
		 *
		 * @param UTC Timestamp $expire_date
		 * @access public
		 */
		function setCartExpirationDate($expire_date) {

			$this->_arr_shopping_cart['shopping-cart']['cart-expiration'] = array('good-until-date' => $expire_date);
		}



		/**
		 * Sets a mercant flat rate shipping charge
		 *
		 * DEPRICATED: Use gShipping Object
		 *
		 * @param string $name
		 * @param decimal $price
		 * @access public
		 */
		function setFlatRateShipping($name, $price, $allowed_restrictions = "", $excluded_restrictions = "") {
			/**
			 * Get shipping object
			 */
			$arr_flat_rate_shipping_obj = $this->_getShippingArray('flat-rate-shipping', $name, $price, $allowed_restrictions, $excluded_restrictions);

			/**
			 * Append to shipping method array
			 */
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods']['flat-rate-shipping'] = $arr_flat_rate_shipping_obj;
		}




		/**
		 * Enter description here...
		 *
		 * @param unknown_type $carrier_options
		 */
		function setCarrierShippingMethods($carrier_options = array()) {

			$str_xml = " <carrier-calculated-shipping-options> ";
			foreach ($carrier_options as $options) {
				$str_xml .= " <carrier-calculated-shipping-option> ";
				if(!empty($options['price'])){
					$str_xml	.= " <price currency=\"USD\">".number_format($options['price'], 2)."</price>";
				}

				if(!empty($options['company'])) {
					$str_xml	.= " <shipping-company>".$options['company']."</shipping-company>";
				}


				if(!empty($options['type'])){
					$str_xml 	.= " <shipping-type>".$options['type']."</shipping-type>";
				}
				$str_xml .= "</carrier-calculated-shipping-option>";
			}

			$str_xml .= "</carrier-calculated-shipping-options>";
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods']['carrier-calculated-shipping'] = $str_xml;
		}

		/**
		 * Set a Pickup Shipping Option
		 *
		 * DEPRICATED: Use gShipping object
		 *
		 * @param string $name
		 * @param decimal $price
		 * @access public
		 */
		function setPickup($name, $price) {

			/**
			 * Get shipping object
			 */
			$arr_pickup = $this->_getShippingArray('pickup', $name, $price);

			/**
			 * Append to shipping method array
			 */
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods']['pickup'] = $arr_pickup;
		}


		/**
		 * Set merchant calculated shipping option.
		 *
		 * DEPRICATED: Use gShipping object
		 * Note: this method isn't fully implemented yet. Merchant calculations require a callback uri.
		 *
		 * @param string $name
		 * @param decimal $price
		 * @param unknown_type $shipping_restrictions
		 * @access public
		 */
		function setMercantCalculatedShipping($name, $price,$allowed_restrictions = "", $excluded_restrictions = "") {

			/**
			 * Get shipping object
			 */
			$arr_merchant_calculated_shipping = $this->_getShippingArray('merchant-calculated-shipping', $name, $price, $allowed_restrictions, $excluded_restrictions);

			/**
			 * Append to shipping method array
			 */
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods']['merchant-calculated-shipping'] = $arr_merchant_calculated_shipping;
		}




		/**
		 * returns shipping-restriction object
		 *
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @return unknown
		 * @access public
		 */
		function getAllowedAreas($country_area, $arr_states, $arr_zips) {
			return  $this->_getAllowedAreas($country_area, $arr_states, $arr_zips);
		}


		/**
		 * returns shipping restriction object
		 *
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @return unknown
		 * @access public
		 *
		 */
		function getExcludedAreas($country_area, $arr_states, $arr_zips) {
			return $this->_getAllowedAreas($country_area, $arr_states, $arr_zips, $type = "excluded");
		}



		/**
		 * Set's the Merchante Checkout Flow Support
		 *
		 * @param unknown_type $edit_cart_url
		 * @param unknown_type $continue_shopping_url
		 * @param unknown_type $request_buyer_phone_number
		 * @param unknown_type $platform_id
		 */
		function setMerchantCheckoutFlowSupport($edit_cart_url ="", $continue_shopping_url = "", $request_buyer_phone_number = false, $platform_id = null) {
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['edit-cart-url'] 		 		= $edit_cart_url;
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['continue-shopping-url'] 		= $continue_shopping_url;
			$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['request-buyer-phone-number'] 	= ($request_buyer_phone_number == true ? 'true' : 'false' );

			if(!empty($platform_id))
				$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['platform-id']					= $platform_id;
		}



		/**
		 * Add Alternate Tax Tables to the cart
		 *
		 * @param array $arr_tax_tables
		 * @access  public
		 */
		function setAlternateTaxTables($arr_tax_tables) {
			$this->_setTaxTables($arr_tax_tables);
		}


		/**
		 * Add a Default Tax Table to the cart
		 *
		 * @param gTaxTable $default_tax_table
		 * @access public
		 */
		function setDefaultTaxTable($default_tax_table){
			$this->_setTaxTables(array($default_tax_table), 'default');
		}



		/**
		 * Add an array of gShipping objects to the cart
		 *
		 * @param array $arr_shipping
		 * @access  public
		 */
		function setShipping($arr_shipping){
			$this->_setShipping($arr_shipping);
		}



		/**
		 * Add a Merchant Calculations object to the array.
		 *
		 * @param gMerchantCalculations $MerchantCalculations
		 * @access public
		 */
		function setMerchantCalculations($MerchantCalculations) {
			if(!empty($MerchantCalculations))
				$this->_merchant_calculations = $MerchantCalculations->getXML();
		}


		/**
		 * Posts cart to Google directly using CURL
		 *
		 * Note: lib_curl and lib_openssl must be installed
		 * on the server to use this alternate mechanism for
		 * posting carts to Google Checkout
		 *
		 * @param unknown_type $xml_cart
		 */
		function postCart($xml_cart) {

		}

		//////////////////////////////////////////////
		// PRIVATE METHODS
		//////////////////////////////////////////////

		/**
		 * Add Tax Tables to the cart
		 *
		 * @param unknown_type $name
		 * @param unknown_type $rate
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @param unknown_type $standalone
		 * @access private
		 */
		function _setTaxTables($arr_tax_tables, $type = 'alternate') {


			/**
			 *  Add Alternate Tax Table
			 */
			$str_xml = "";

			/**
			 * Iterate over each tax table
			 */
			foreach ($arr_tax_tables as $tax_table){
				$str_xml .= $tax_table->getXML();
			}

			/**
			 * Add Tax Table XML to the cart
			 */
			if($type =='alternate') {
				$this->_tax_tables .= "<alternate-tax-tables>$str_xml</alternate-tax-tables>";
			}else if($type == 'default')
				$this->_tax_tables .= $str_xml;

		}


		/**
		 * Sets the Shipping objects in the cart.
		 *
		 * @param unknown_type $arr_shipping
		 */
		function _setShipping($arr_shipping) {

			$str_xml = "";
			foreach($arr_shipping as $Shipping) {
				$str_xml .= $Shipping->getXML();
			}
			$this->_shipping = $str_xml;
		}

		function setTaxCalculation($calculate = 'true')
		{
			$this->_shipping .= '<tax>' . $calculate . '</tax>';
		}

		/**
		 * Enter description here...
		 *
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @param unknown_type $type
		 */
		function _getAllowedAreas($country_area, $arr_states, $arr_zips, $type="allowed"){

			$arr_areas = array(
									"$type-areas" => array()
								);


			if(!empty($country_area)) {
				$arr_areas["$type-areas"]['us-country-area'] = array('_attributes' => array('country-area' => $country_area));
			}


			/**
			 * if we have states to allow / exclude
			 */
			if(!empty($arr_states)) {
				foreach ($arr_states as $state) {
					/**
					 * Bit of a hack since the XML_Serializer does not allow
					 * more than one 'default' repeatable tags.
					 *
					 * Google Has decided this crazy markup
					 */
					$state_data .= " <us-state-area>
										<state>
											".$state."
										</state>
									</us-state-area>";
				}
				$arr_areas["$type-areas"] = $state_data;
			}

			/**
			 * if we have zips to allow / exclude
			 */
			if(!empty($arr_zips)) {
				$zip_serializer = new XML_Serializer($this->_zip_serializer_options);
				$zip_serializer->serialize($arr_zips);
				$arr_areas["$type-areas"]['us-zip-area'] = $this->_removeTag($zip_serializer->getSerializedData());
			}

			return $arr_areas;
		}

		/**
		 * Enter description here...
		 *
		 * @param unknown_type $shipping_type
		 * @param unknown_type $name
		 * @param unknown_type $price
		 * @param unknown_type $shipping_restrictions
		 * @return unknown
		 */
		function _getShippingArray($shipping_type, $name, $price, $allowed_restrictions = "", $excluded_restrictions = "") {
			/**
			 * Check if there exists a shiping-methods
			 */
			if(empty($this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods'])) {
				$this->_arr_shopping_cart['checkout-flow-support']['merchant-checkout-flow-support']['shipping-methods'] = array();
			}


			/**
			 * Build Flat Rate Shipping Method Element
			 */
			$arr_shipping_obj = array(
											'price' => array(
														'_attributes' => array('currency' => $GLOBALS['GCheckout_currency']),
														'_content'    => $price),

											 '_attributes' => array('name' => $name)

									);

			/**
			 * Add shipping restrictions (allowed / excluded)
			 */
			if(!empty($allowed_restrictions)) {
				$arr_shipping_obj['shipping-restrictions']['allowed-areas'] = $allowed_restrictions['allowed-areas'];
			}


			if(!empty($excluded_restrictions)) {
				$arr_shipping_obj['shipping-restrictions']['excluded-areas'] = $excluded_restrictions['excluded-areas'];
			}

			return $arr_shipping_obj;
		}

		/**
		 * Private: Sets the XML_Serializer Options for the GCheckout XML format
		 *
		 */
		function _setSerializerOptions() {
			$this->_serializer_options =  array("addDecl"=> true,
												"indent"=>"     ",
												"encoding" =>"UTF-8",
												"rootName" => 'checkout-shopping-cart',
												"rootAttributes" 	 => array("xmlns"=> $GLOBALS['GCheckout_xmlSchema']),
												"scalarAsAttributes" => false,
				                        		"attributesArray"    => '_attributes',
				                        		"contentName"        => '_content',
				                        		"defaultTagName"	 => 'item',
				                        		"replaceEntities"    => XML_SERIALIZER_ENTITIES_NONE
												);
			$this->_state_serializer_options =  array("addDecl"=> false,
												"indent"=>" ",
												"rootName" => "REMOVE",
												"scalarAsAttributes" => false,
				                        		"attributesArray"    => '_attributes',
				                        		"contentName"        => '_content',
				                        		"defaultTagName"	 => 'state'
												);
			$this->_zip_serializer_options =  array("addDecl"=> false,
												"indent"=>" ",
												"rootName" =>"REMOVE",
												"scalarAsAttributes" => false,
				                        		"attributesArray"    => '_attributes',
				                        		"contentName"        => '_content',
				                        		"defaultTagName"	 => 'zip-pattern'
												);
			$this->_state_area_serializer_options =  array("addDecl"=> false,
												"indent"=>" ",
												"rootName" =>"REMOVE",
												"scalarAsAttributes" => false,
				                        		"attributesArray"    => '_attributes',
				                        		"contentName"        => '_content',
				                        		"defaultTagName"	 => 'us-state-area'
												);
			$this->_alt_tax_table_serializer_options =  array("addDecl"=> false,
												"indent"=>" ",
												"rootName" =>"REMOVE",
												"scalarAsAttributes" => false,
				                        		"attributesArray"    => '_attributes',
				                        		"contentName"        => '_content',
				                        		"defaultTagName"	 => 'alternate-tax-table'
												);
		}

		/**
		 * Private: Initializes the base shopping cart array
		 *
		 */
		function _setShoppingCart() {
			$this->_arr_shopping_cart	= array(
									'shopping-cart' => array(),
									'checkout-flow-support' => array(
															'merchant-checkout-flow-support' => array()
															)
							);
		}



		/**
		 * Hash function that computes HMAC-SHA1 value.
		 * This function is used to produce the signature
		 * that is reproduced and compared on the other end
		 * for data integrity.
		 *
		 * @param	$data		message data
		 * @param	$merchant_key	secret Merchant Key
		 * @return	$hmac		value of the calculated HMAC-SHA1
		 */
		function _getHmacSha1($data, $merchant_key) {

		    $blocksize = 64;
		    $hashfunc = 'sha1';

		    if (strlen($merchant_key) > $blocksize) {
		        $merchant_key = pack('H*', $hashfunc($merchant_key));
		    }

		    $merchant_key = str_pad($merchant_key, $blocksize, chr(0x00));
		    $ipad = str_repeat(chr(0x36), $blocksize);
		    $opad = str_repeat(chr(0x5c), $blocksize);
		    $hmac = pack(
		                    'H*', $hashfunc(
		                            ($merchant_key^$opad).pack(
		                                    'H*', $hashfunc(
		                                            ($merchant_key^$ipad).$data
		                                    )
		                            )
		                    )
		                );
		    return $hmac;
		}


		/**
		 * Enter description here...
		 *
		 * @param unknown_type $input
		 * @return unknown
		 */
		function _removeTag($input) {
			return str_replace($this->_remove_tags,"", $input);
		}


		/**
		 *  CODE BORROWED FROM GOOGLE'S SAMPLE CODE
		 *  - Thanks Google!!
		 *
		 * The GetCurlResponse function sends an API request to Google Checkout
		 * and returns the response. The HTTP Basic Authentication scheme is
		 * used to authenticate the message.
		 *
		 * This function utilizes cURL, client URL library functions.
		 * cURL is supported in PHP 4.0.2 or later versions, documented at
		 * http://us2.php.net/curl
		 *
		 * @param    $request     XML API request
		 * @param    $post_url    URL address to which the request will be sent
		 * @return   $response    synchronous response from the Google Checkout
		 *                            server
		 */

		function _getCurlResponse($request, $post_url) {

			/**
			 * Check if Curl is installed
			 */
			if(!function_exists('curl_setopt'))
				return false;

		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $post_url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, VALIDATE_MY_SSL_CERT);
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, VALIDATE_GOOGLE_SSL_CERT);

		    /*
		     * This "if" block, which sets the HTTP Basic Authentication scheme
		     * and HTTP headers, only executes for Order Processing API requests
		     * and for server-to-server Checkout API requests.
		     */

	        // Set HTTP Basic Authentication scheme
	        curl_setopt($ch, CURLOPT_USERPWD, $this->_mercant_id .
	            ":" . $this->_mercant_key);

	        // Set HTTP headers
	        $header = array();
	        $header[] = "Content-type: application/xml";
	        $header[] = "Accept: application/xml";
	        $header[] = "Content-Length: ".strlen($request);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);



		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

		    // Execute the API request.
		    $response = curl_exec($ch);

		    /*
		     * Verify that the request executed successfully. Note that a
		     * successfully executed request does not mean that your request
		     * used properly formed XML.
		     */
		    if (curl_errno($ch)) {
		       	/* Do Something */
		       	 echo "A problem occured when posting your cart to google <br/>";
		      	 echo "CURL_ERROR: ".curl_errno($ch);
		      	 return false;	/* No Open SSL */
		    } else {
		        curl_close($ch);
		    }

		    // Return the response to the API request
		    return $response;
		}



	} // END CLASS DEFINITION

?>