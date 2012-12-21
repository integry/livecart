<?php
/**
 *
 *
 * @param array $params
 *		(string)id => Field id (This field is required)
 *		(string)format => Date format (default: %d-%b-%Y)
 *		(bool)noform => Sometimes calendar must be put not inside the form, or dinamically. You should pass noForm=true if you don't want to depend on form
 *		(bool)nobutton => Don't show button, use display input as trigger
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty.form
 * @author Integry Systems
 */
function smarty_function_calendar($params, $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	$params['nobutton'] = array_key_exists('nobutton', $params) ?!!$params['nobutton'] : false;
	if(!isset($params['noform']))
	{
		$formParams = $smarty->_tag_stack[0][1];
		$formHandler = $formParams['handle'];
		if (!($formHandler instanceof Form))
		{
			throw new HelperException('Element must be placed in {form} block');
		}
		$fieldName = $params['name'];
	}

	$id = isset($params['id']) ? $params['id'] : uniqid();
	unset($params['id']);

	$params['format'] = isset($params['format']) ? $params['format'] : "%d-%b-%Y";
	$format = $params['format'];
	unset($params['format']);

	if(isset($params['noform']))
	{
		$value = $params['value'];
		$fieldName = $params['name'];
	}
	else
	{
		$value = $formHandler->get($fieldName);
	}
	unset($params['noform']);
	unset($params['value']);
	unset($params['name']);

	$params['class'] = !isset($params['class']) ? 'date' : $params['class']. ' date';
	$params['class'] .= ' span2';

	if (!empty($params['nobutton']))
	{
		unset($params['nobutton']);
	}

	$params['readonly'] = true;

	$output = '<div class="input-append date" id="' . $id .'" data-date="' . $value . '" data-date-format="dd-mm-yyyy">';

	$output .= '<input type="text" value="'.$value.'" name="'.$fieldName.'"';
	foreach ($params as $n => $v)
		$output .= ' ' . $n . '="' . $v . '"';
	$output .= "/>";

	$output .= '<span class="add-on"><i class="icon-th"></i></span></div>';

	$output .= "<script type='text/javascript'>jQuery('#" . $id . "').bootstrap_datepicker();</script>";

	return $output;
}

?>