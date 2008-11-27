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
	 * Google Checkout Tax Rule Primitive
	 *
	 * Google Checkout API Tax Tables are agregated into tax rules.
	 * ie. Tax Tables 1-* Tax Rules.
	 *
	 * This class is the abstraction of a Google Checkout Tax Rule object.
	 *
	 * @author Ron Howard
	 * @copyright Expert Database Solutions, LLC 2006
	 *
	 */
	class gTaxRule {
		var $_rate;
		var $_country_area;
		var $_arr_states;
		var $_arr_zips;
		var $_type;
		var $_shipping_taxed;


		/**
		 * Google Checkout Tax Rule Constructor
		 *
		 * Note: $type parameter is here just for unit testing
		 * during construction of the tax table. The rule will
		 * be set to the type of the tax table.
		 *
		 * ie if a default tax table calls the getXML() method
		 * then the tax rule will be set to type = 'default'
		 *
		 * @param unknown_type $rate
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @param unknown_type $type
		 * @return gTaxRule
		 */
		function gTaxRule($rate, $country_area = true, $arr_states = null, $arr_zips = null, $type="default") {
			$this->_rate = $rate;
			$this->_country_area = $country_area;
			$this->_arr_states   = $arr_states;
			$this->_arr_zips 	 = $arr_zips;
			$this->_type 		 = $type;
			$this->_shipping_taxed = null;
		}


		/**
		 * Are we taxing the shipping as well
		 *
		 * @param unknown_type $shipping_taxed
		 */
		function setShippingTaxed($shipping_taxed = 'false'){
			$this->_shipping_taxed = $shipping_taxed;
		}

		/**
		 * Returns the XML Representation of tax rule
		 *
		 * @return XML representation of a tax rul
		 * @access public
		 */
		function getXML() {

			$str_xml = " <$this->_type-tax-rule>";
			$str_xml .= "	<rate>$this->_rate</rate>";

			if(!empty($this->_shipping_taxed) && $this->_type == TAX_TABLE_DEFAULT)
				$str_xml .= "	<shipping-taxed>$this->_shipping_taxed</shipping-taxed>";

			/**
			 * Check for restrictions
			 */
			if(!empty($this->_country_area) || !empty($this->_arr_states) || !empty($this->_arr_zips)){
				$str_xml .= "<tax-area>";

				/**
				 * Country Area
				 */
				if(!empty($this->_country_area))
					$str_xml .= '<world-area/>';
					//$str_xml .= "<us-country-area country-area=\"$this->_country_area\" />";

				/**
				 * State Areas
				 */
				if(!empty($this->_arr_states)) {
					foreach($this->_arr_states as $state){
						$str_xml .="<us-state-area><state>$state</state></us-state-area>";
					}
				}

				/**
				 * Zip Areas
				 */
				if(!empty($this->_arr_zips)) {
					foreach($this->_arr_zips as $zip){
						$str_xml .= "<us-zip-area><zip-pattern>$zip</zip-pattern></us-zip-area>";
					}
				}

				$str_xml .= "</tax-area>";
			}

			/**
			 * close tax rule
			 */
			$str_xml .="</$this->_type-tax-rule>";
			return $str_xml;
		}
	}
?>