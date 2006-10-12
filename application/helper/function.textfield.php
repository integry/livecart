<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_textfield($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$handle = $formParams['handle'];
	$fieldName = $params['name'];
	
	$content = '<input type="text"';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}
	$content .= ' validate="' . $handle->getValidator()->getJSValidatorParams($fieldName) . '"'; 
	$content .= '/>';
	
	return $content;
}

?>