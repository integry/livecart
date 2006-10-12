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
function smarty_block_form($params, $content, $smarty, &$repeat) 
{
	$handle = $params['handle'];
	unset($params['handle']);
	if (!($handle instanceof Form)) 
	{
		throw new HelperException("Form must have a Form instance assigned!");
	}
	
	$formAction = $params['action'];
	unset($params['action']);
	$vars = explode(" ", $formAction);
	$URLVars = array();
	
	foreach ($vars as $var)
	{
		$parts = explode("=", $var);
		$URLVars[$parts[0]] = $parts[1];
	}
	
	$router = Router::getInstance();
	$actionURL = $router->createURL($URLVars);
	
	$formAttributes ="";
	foreach ($params as $param => $value)
	{
		$formAttributes .= $param . '="' . $value . '"';
	}
	
	$form = '<form action="'.$actionURL.'" '.$formAttributes.'>';
	$form .= $content;
	$form .= "</form>";
	return $form;
}

?>