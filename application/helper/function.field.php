<?php

/**
 * Creates html form field by using HTMLDisplayer instance
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_field($params, $smarty) {
	
	$formParams = $smarty->_tag_stack[0][1];

	$displayHandler = $formParams['handler'];
	$fieldName = $params['name'];
	
	$fieldDisplayer = $displayHandler->Field($fieldName);
	foreach ($params as $name => $param) {
		$fieldDisplayer->SetAttribute(FormFieldDisplayerHTML::FIELD, $name, $param);
	}

	return $fieldDisplayer->Display();
}

?>