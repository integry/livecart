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
function smarty_function_selectfield($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$handle = $formParams['handle'];
	
	$options = $params['options'];
	if (empty($options))
	{
		$options = array();
	}
	unset($params['options']);
	
	$content = '<select';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}
	$content .= '>\n';
	
	$fieldValue = $handle->getValue($params['name']);
	foreach ($options as $value => $title)
	{
		if ($fieldValue == $value)
		{
			$content .= "\t" . '<option value="' . $value . '" selected>' . $title . '</option>' . "\n";
		}
		else
		{
			$content .= "\t" . '<option value="' . $value . '">' . $title . '</option>' . "\n";
		}
	}
	$content .= "</select>";
	
	return $content;
}

?>