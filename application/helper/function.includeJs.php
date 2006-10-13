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
function smarty_function_includeJs($params, $smarty) 
{
	$fileName = $params['file'];
	$code = '<script src="/livecart/public/javascript/' . $fileName . '" media="screen" type="text/javascript"></script>' . "\n";
	$currentContent = $smarty->get_template_vars("JAVASCRIPT");
	
	$smarty->assign("JAVASCRIPT", $currentContent . $code);
}

?>