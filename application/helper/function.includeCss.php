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
function smarty_function_includeCss($params, LiveCartSmarty $smarty) 
{
	$fileName = $params['file'];
	
	// fix slashes
	$fileName = str_replace(chr(92),'/', $fileName);
	
	$code = '<link href="stylesheet/' . $fileName . '" media="screen" rel="Stylesheet" type="text/css"/>' . "\n";
	$currentContent = $smarty->get_template_vars("STYLESHEET");
	$smarty->assign("STYLESHEET", $currentContent . $code);
}

?>