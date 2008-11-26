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
	 * Representation of a Shopping cart item.
	 *
	 */
	class  gItem {
		var $_name;
		var $_description;
		var $_price;
		var $_quantity;
		var $_private_item_data;
		var $_tax_table_selector;
		var $_digital_type;
		var $_digital_description;
		var $_digital_key;
		var $_digital_url;
		var $_weight;
		
		
		/**
		 * Default Constructor
		 *
		 * @param unknown_type $name
		 * @param unknown_type $description
		 * @param unknown_type $price
		 * @param unknown_type $quantity
		 * @param string XML Document $private_item_data
		 * @return gItem
		 */
		function gItem($name, $description, $quantity, $price, $tax_table_selector = "", $private_item_data = "") {
			$this->_name 			= $name;
			$this->_description  	= $description;
			$this->_price			= $price;
			$this->_quantity		= $quantity;
			$this->_private_item_data = $private_item_data;
			$this->_tax_table_selector= $tax_table_selector;
		}
		
		
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $weight
		 */
		function setWeight($weight) {
			$this->_weight = $weight;
		}
		
		
		/**
		 * Enter description here...
		 *
		 * @param unknown_type $type
		 * @param unknown_type $description
		 * @param unknown_type $key
		 * @param unknown_type $url
		 */
		function setDigitalDelivery($type = DIGITAL_EMAIL_DELIVERY, $description='', $key='', $url=''){
			$this->_digital_type = $type;
			$this->_digital_description = $description;
			$this->_digital_key = $key;
			$this->_digital_url = $url;
		}
		
		
		
		
		/**
		 * Enter the name of the alternate tax table that you want 
		 * to use to calculate tax for this item.
		 *
		 * @param unknown_type $tax_table_selector
		 */
		function setTaxTableSelector($tax_table_selector){
			$this->_tax_table_selector = $tax_table_selector;
		}
		
		
		/**
		 * Add any valid XML document to the item.
		 *
		 * @param unknown_type $private_item_data
		 */
		function setPrivateItemData($private_item_data){
			$this->_private_item_data = $private_item_data;
		}
		
		/**
		 * return Google Checkout XML 
		 *
		 * @return unknown
		 */
		function getXML() {
			$currency 	= $GLOBALS['GCheckout_currency'];
			$str_xml 	= " <item>";
			$str_xml	.= "		<item-name>$this->_name</item-name>";
			$str_xml	.= "		<item-description>$this->_description</item-description>";
			$str_xml	.= "		<unit-price currency=\"$currency\">$this->_price</unit-price>";
			$str_xml	.= "		<quantity>$this->_quantity</quantity>";
			
			if(!empty($this->_tax_table_selector))
				$str_xml .= "	<tax-table-selector>$this->_tax_table_selector</tax-table-selector>";
			
			if(!empty($this->_private_item_data))
				$str_xml	.= "		<merchant-private-item-data>$this->_private_item_data</merchant-private-item-data>";
			
				
			if(!empty($this->_weight) && $this->_weight > 0) {
				$str_xml 	.= "	<item-weight unit=\"LB\">".number_format($this->_weight, 2)."</item-weight> ";
			}
			
			if(!empty($this->_digital_type)) {
				$str_xml .= "	<digital-content> ";
				
				if($this->_digital_type == DIGITAL_EMAIL_DELIVERY){
					$str_xml .= " <email-delivery>true</email-delivery> ";	
				}
				elseif ($this->_digital_type == DIGITAL_KEY_URL_DELIVERY){
					$str_xml .= " <description>".$this->_digital_description."</description> ";
					$str_xml .= " <key>".$this->_digital_key."</key> ";
					$str_xml .= " <url>".$this->_digital_url."</url> ";
				}
				
				
				$str_xml .= "	</digital-content> ";
			}
			
			
			$str_xml	.= " </item>";
			return $str_xml;
		}
	}
?>