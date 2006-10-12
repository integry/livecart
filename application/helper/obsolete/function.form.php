<?php

/**
 * Render a form by using a default rendering method (hardcoded)
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * 
 */
function smarty_function_form($params, $smarty) {
	$formObj = $params['form'];
	
	if (!($formObj instanceof Form)) {
		throw new HelperException("Template variable 'form' must be an instance of Form class");
	}
	unset($params['form']);
	foreach ($params as $name => $value) {
		$form->setAttribute($name, $value);
	}
	
	// iterating form elements
	return $formObj->render();
}

?>