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
	 * Google Checkout Tax Table Primitive
	 *
	 * 
	 * This class is the abstraction of a Google Checkout Tax Table object.
	 * 
	 * @author Ron Howard
	 * @copyright Expert Database Solutions, LLC 2006
	 * 
	 */
	class gTaxTable {
		var $_arr_tax_rules;
		var $_type;
		var $_name;
		var $_standalone;
		
		
		
		/**
		 * Default Constructor
		 *
		 * @param unknown_type $rate
		 * @param unknown_type $country_area
		 * @param unknown_type $arr_states
		 * @param unknown_type $arr_zips
		 * @param unknown_type $type
		 * @return gTaxTable
		 */
		function gTaxTable($name, $arr_tax_rules, $type = 'alternate', $standalone = 'false') {
			$this->_arr_tax_rules = $arr_tax_rules;
			$this->_name  		= $name;
			$this->_standalone  = $standalone;	
			$this->_type = $type;
		}
		
		
		
		/**
		 * Returns the XML representation of the Tax Table.
		 * 
		 * @return XML Representation of Tax Table.
		 * @access  public.
		 *
		 */
		function getXML() {
			
			$str_xml = " <$this->_type-tax-table ";
			
			if($this->_type == 'alternate')
				$str_xml .= " standalone=\"$this->_standalone\" name=\"$this->_name\" ";

			$str_xml .= ">";
			
			
			if(!empty($this->_arr_tax_rules)){
			
				/**
				 * Omit if default type
				 */	
				if($this->_type != 'default')
					$str_xml .= "<$this->_type-tax-rules>";
				else 
					$str_xml .= "<tax-rules>";
				
				/**
				 * For each tax rule
				 */
				foreach ($this->_arr_tax_rules as $tax_rule){
					
					$tax_rule->_type = $this->_type;			//Tax Rule must be the same type as the table
					$str_xml .= $tax_rule->getXML();			//Add tax rule xml to the string.
					
					/**
					 * If the table is default tax table we can only have one default
					 * tax rule. Break after first.
					 */
					if($this->_type == 'default')
						break;				
				}
				
				/**
				 * Omit if default type
				 */
				if($this->_type != 'default')
					$str_xml .= "</$this->_type-tax-rules>";
				else 
					$str_xml .= "</tax-rules>";
			}
				
			
				
			$str_xml .= "</$this->_type-tax-table>";
			return $str_xml;	
		}
	}
?>