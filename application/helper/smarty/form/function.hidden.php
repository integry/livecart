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
function smarty_function_hidden($params, $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	$fieldName = $params['name'];

	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}

	if (!empty($formParams['model']))
	{
		$params['ng-model'] = $formParams['model'] . '.' . $params['name'];
	}

	$fieldName = $params['name'];

	$output = '<input type="hidden"';

	if (!isset($params['value']))
	{
		$params['value'] = $formHandler->get($fieldName);
	}

	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
	}

	$output .= " />";

	return $output;
}

?>