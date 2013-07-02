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
function smarty_function_checkbox($params, $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	if (empty($params['id']))
	{
		$params['id'] = uniqid();
	}

	$smarty->assign('last_fieldType', 'checkbox');
	$smarty->assign('last_fieldID', $params['id']);

	if (empty($params['class']))
	{
		$params['class'] = 'checkbox';
	}
	else
	{
		$params['class'] .= ' checkbox';
	}

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}
	$fieldName = $params['name'];

	if(!isset($params['value']))
	{
		$params['value'] = 1;
	}

	if (!empty($formParams['model']))
	{
		$params['ng-model'] = $formParams['model'] . '.' . $params['name'];
	}

	// Check permissions
	if($formParams['readonly'])
	{
		$params['disabled'] = 'disabled';
	}

	$formValue = $formHandler->get($fieldName);

	// get checkbox state if the form has been submitted
	if (1 == $formHandler->get('checkbox_' . $fieldName))
	{
		if ($formValue == $params['value'] || ('on' == $params['value'] && 1 == $formValue))
		{
			$params['checked'] = 'checked';
		}
		else
		{
			unset($params['checked']);
		}
	}
	else if ($params['value'] == $formValue)
	{
		$params['checked'] = 'checked';
	}

	if (empty($params['checked']))
	{
		unset($params['checked']);
	}

	$output = '<input type="checkbox"';
	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . $value . '"';
	}

	$output .= '/><input type="hidden" name="checkbox_' . $params['name'] . '" value="1" />';

	return $output;
}

?>