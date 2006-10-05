<?php

/**
 * Creates form view automaticaly (no need to call form field helper)
 * This function does not allow a designed to customize form layout in any way.
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_autoform($params, $smarty) {

	$js = $smarty->get_template_vars('JAVASCRIPT');
	if (!empty($js)) {
		if (!in_array('javascript/formValidator.js', $js)) {
			$smarty->append("JAVASCRIPT", 'javascript/formValidator.js');
		}
	} else {
		$smarty->append("JAVASCRIPT", 'javascript/formValidator.js');
	}
	
	$displayHandler = $params['handler'];
	
	$actionUrl = Router::createURL(array("action" => $params['action'], "controller" => $params['controller']));
	$displayHandler->SetAction($actionUrl);
	
	if (!empty($params['method'])) {
		$displayHandler->SetMethod($params['method']);
	} else {
		$displayHandler->SetMethod("POST");
	}
	
	$displayHandler->SetEncType('multipart/form-data');

	return $displayHandler->Display();
}

?>