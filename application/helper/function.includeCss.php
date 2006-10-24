<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_includeCss($params, $smarty) 
{
	$fileName = $params['file'];
	$code = '<link href="stylesheet/' . $fileName . '" media="screen" rel="Stylesheet" type="text/css"/>' . "\n";
	$currentContent = $smarty->get_template_vars("STYLESHEET");
	$smarty->assign("STYLESHEET", $currentContent . $code);
}

?>