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
	$currentContent = $smarty->get_template_vars("JAVASCRIPT");
	if (strpos($currentContent, $fileName) === false)
	{
		$code = '<script src="/livecart/public/javascript/' . $fileName . '" type="text/javascript"></script>' . "\n";
		$smarty->assign("JAVASCRIPT", $currentContent . $code);
	}
}

?>