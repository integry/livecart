<?php

/**
 * Smarty block plugin, for creating form and its sections.
 * Used together with section function.
 * Using just is such way:
 * <code>
 * 
 * {form}
 *		{section num=1}
 *		<tr><td colspan="2">BLA BLA BLA</td></tr> 		
 * 		{sectio num=2}
 * {/form}
 * 
 * </code>
 *
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 * @author Denis Slaveckij <denis@integry.net>
 */
function smarty_block_form($params, $content, $smarty, &$repeat) {
	
	$validator = $params['validator'];
	if (!($validator instanceof DataValidator)) {
		throw new HelperException("Form must have a validator assigned!");
	}
	
	$formAction = $params['action'];
	$vars = explode(" ", $formAction);
	$URLVars = array();
	
	foreach ($vars as $var) {
		$parts = explode("=", $var);
		$URLVars[$parts[0]] = $parts[1];
	}
	
	$router = Router::getInstance();
	$actionURL = $router->createURL($URLVars);
	
	$form = '<form action="'.$actionURL.'">';
	$form .= $content;
	$form .= "</form>";
	return $form;
	
	/*
	$js = $smarty->get_template_vars('JAVASCRIPT');
	
	if (!empty($js)) {
	
		if (!in_array('javascript/formValidator.js', $js)) {
	
			$smarty->append("JAVASCRIPT", 'javascript/formValidator.js');
		}
	} else {
	
		$smarty->append("JAVASCRIPT", 'javascript/formValidator.js');
	}
	
	$form = $params['handler'];

	if (!($form instanceof Form)) {
		
		throw new HelperException("Template variable 'form' must be an instance of Form class");
	}
	
	return $form->renderHeader().
		"<table>".$content."</table>".
		$form->renderFooter();	
	
	*/
}

?>