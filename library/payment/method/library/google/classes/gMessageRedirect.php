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
	 * Google Checkout Redirect Message Parser
	 * 
	 * @author  	David Machiavello
	 * @copyright   Expert Database Solutions, LLC &copy; 2006
	 * 
	 */
	class gMessageRedirect
	{
		
		/**
		 * Holds message to be parsed
		 *
		 * @var mixed
		 */
		var $message;
		
		/**
		 * XML_Unserializer object
		 *
		 * @var PEAR XML_Unserializer Obj
		 */
		var $xml_parser;
		
		/**
		 * Parsed XML from the Unserializer
		 *
		 * @var associative array
		 */
		var $parsed_response;
		
		/**
		 * Stores the redirect URL after parsing
		 *
		 * @var string
		 */
		var $redirect_url;
		
		
		/**
		 * Constructor
		 *
		 * @param mixed $data (xml data)
		 */
		function gMessageRedirect($data)
		{
			$this->message = $data;
			$this->parseMessage();
		}
		
		/**
		 * Parses message provided to the constructor and populates 
		 * the object's parameters
		 * 
		 * @return void
		 */
		function parseMessage()
		{
			$this->xml_parser = new XML_Unserializer();
			$this->xml_parser->unserialize($this->message);
			$this->parsed_response = $this->xml_parser->getUnserializedData();
			$this->redirect_url = $this->parsed_response['redirect-url'];
		}
		

	}
?>