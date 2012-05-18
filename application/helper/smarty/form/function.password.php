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
function smarty_function_password($params, $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}
	$fieldName = $params['name'];

	$output = '<input type="password"';

	// Check permissions
	if($formParams['readonly'])
	{
		$params['disabled'] = 'disabled';
	}

	if (isset($params['value']))
	{
		unset($params['value']);
	}

	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
	}

	$output .= " />";

	return $output;
}

?>