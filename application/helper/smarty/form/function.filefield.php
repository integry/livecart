<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper.smarty.form
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_filefield($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	$fieldName = $params['name'];
	
	// this should never be done. ID is should always be unique value. 
    // Doing so breaks lots of javascript traversing. It misleads prototype
    // And I found it also introduces some errors in tinyMCE. If you
    // realy want such functionality come up with some clever way to generate
    // unique id.
//	if (!isset($params['id']))
//	{
//	  	$params['id'] = $params['name'];
//	}
	
	// Check permissions
	if($formParams['readonly'])
	{	
        $params['disabled'] = 'disabled';
	}
	
	$content = '<input type="file"';
	foreach ($params as $name => $param) 
	{
		$content .= ' ' . $name . '="' . $param . '"'; 
	}

	$content .= '/>';
	
	return $content;
}

?>