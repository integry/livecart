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
	 * Enter description here...
	 *
	 */
	class gShipping {
		var $_name;
		var $_type; 						/*flat-rate, pickup or merchant-calculated-shipping */
		var $_arr_allowed_states;
		var $_allowed_country;
		var $_arr_allowed_zips;
		var $_arr_excluded_states;
		var $_excluded_country;
		var $_arr_excluded_zips;
		var $_price;
		var $_arr_validate_shipping_restrictions;
		
		
		/**
		 * Default Constructor
		 *
		 * @param unknown_type $name
		 * @param unknown_type $type
		 * @return gShipping
		 */
		function gShipping($name, $price, $type) {
			$this->_name = $name;
			$this->_type = $type;
			$this->_price = $price;
			
			/**
			 * Set Keys for Validating Shipping Restrictions
			 */
			$this->_arr_validate_shipping_restrictions = array('_allowed_country',
															   '_excluded_country',
															   '_arr_allowed_states',
															   '_arr_allowed_zips',
															   '_arr_excluded_states',
															   '_arr_excluded_zips');
		}
		
		
		/**
		 * Add Allowed Areas to Shipping
		 *
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_state_areas
		 * @param unknown_type $arr_zip_areas
		 */
		function addAllowedAreas($country_area = null, $arr_state_areas = null, $arr_zip_areas = null) {
			$this->_allowed_country 		= $country_area;
			$this->_arr_allowed_states 		= $arr_state_areas;
			$this->_arr_allowed_zips 		= $arr_zip_areas;
		}
		
		
		/**
		 * Add Excluded Areas to Shipping
		 *
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_state_areas
		 * @param unknown_type $arr_zip_areas
		 */
		function addExcludedAreas($country_area = null, $arr_state_areas = null, $arr_zip_areas = null) {
			$this->_excluded_country  = $country_area;
			$this->_arr_excluded_states = $arr_state_areas;
			$this->_arr_excluded_zips 	= $arr_zip_areas;

		}
	
		
		/**
		 * get Google Checkout XML 
		 *
		 */
		function getXML() {
			$currency = $GLOBALS['GCheckout_currency'];
			$str_xml = " <$this->_type name=\"$this->_name\" >";
			$str_xml .= "	<price currency=\"$currency\" >$this->_price</price>";
			
			
			/**
			 * Check if we have any shipping restrictions
			 */
			$has_shipping_restrictions = false;
			foreach ($this->_arr_validate_shipping_restrictions as $property_name){
				if(!empty($this->$property_name)) $has_shipping_restrictions = true;;
			}
			
			if($has_shipping_restrictions) {
				$str_xml .= " <shipping-restrictions>";
				
				/**
				 * Check for allowed areas
				 */
				if(!empty($this->_allowed_country) || !empty($this->_arr_allowed_states) || !empty($this->_arr_allowed_zips)){
					$str_xml .= $this->_getAllowedExcludedAreas($this->_allowed_country, $this->_arr_allowed_states, $this->_arr_allowed_zips, 'allowed');
				}
				
				
				/**
				 * Check for excluded areas
				 */
				if(!empty($this->_excluded_country) || !empty($this->_arr_excluded_states) || !empty($this->_arr_excluded_zips)){
					$str_xml .= $this->_getAllowedExcludedAreas($this->_excluded_country, $this->_arr_excluded_states, $this->_arr_excluded_zips, 'excluded');
				}
				

				$str_xml .= " </shipping-restrictions>";
			}
			
			$str_xml .= " </$this->_type>";
			return $str_xml;
		}
		
		
		
		/**
		 * Builds XML structure for Shipping Restrictions
		 *
		 * @param unknown_type $country
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @param unknown_type $type
		 * @return unknown
		 */
		function _getAllowedExcludedAreas($country, $arr_states, $arr_zips, $type) {
			
			$str_xml = "";
			if(!empty($country) || !empty($arr_states) || !empty($arr_zips)){
					$str_xml .= "	<$type-areas>";
					
						/**
						 * us-country-area
						 */
						if(!empty($country))
							$str_xml .= $this->_getCountryArea($country);
							
						/**
						 * us-sate-areas
						 */
						if(!empty($arr_states))
							$str_xml .= $this->_getUsStateArea($arr_states);
							
						/**
						 * us-zip-areas
						 */
						if(!empty($arr_zips))
							$str_xml .= $this->_getUsZipArea($arr_zips);
							
					$str_xml .= "	</$type-areas>";
				}
			return $str_xml;
		}
		
		/**
		 * Builds XML structure for Shipping Restrictions
		 *
		 * @param unknown_type $country_area
		 * @return unknown
		 */
		function _getCountryArea($country_area) {
			return "	<us-country-area country-area=\"$country_area\" />";
		}
		
		
		/**
		 * Builds XML structure for Shipping Restrictions
		 *
		 * @param unknown_type $arr_state_aras
		 * @return unknown
		 */
		function _getUsStateArea($arr_state_aras) {
			$str = "";
			
			foreach ($arr_state_aras as $state) {
				$str .= " <us-state-area> 
							<state>$state</state>
						 </us-state-area> ";
			}
			return $str;
		}
		
		
		/**
		 * Builds XML structure for Shipping Restrictions
		 *
		 * @param unknown_type $arr_zip_areas
		 * @return unknown
		 */
		function _getUsZipArea($arr_zip_areas) {
			$str = "";
			
			foreach ($arr_zip_areas as $zip) {
				$str .= " <us-zip-area> 
							<zip-pattern>$zip</zip-pattern>
						 </us-zip-area> ";
			}
			return $str;
		}
	}
?>