<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty.form
 * @author Integry Systems
 */
function smarty_function_filefield($params, $smarty)
{
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	$fieldName = $params['name'];

	// Check permissions
	if($formParams['readonly'])
	{
		$params['disabled'] = 'disabled';
	}

	$formHandler->setParam('enctype', 'multipart/form-data');

	$content = '<input type="file"';
	foreach ($params as $name => $param)
	{
		$content .= ' ' . $name . '="' . $param . '"';
	}

	$content .= '/>';

	return $content;
}

?>