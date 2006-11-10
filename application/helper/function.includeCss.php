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
	
	// fix slashes
	$fileName = str_replace(chr(92),'/', $fileName);
	
	// check if file exists
	if (!file_exists(ClassLoader::getRealPath('public.stylesheet.') . $fileName))
	{
		return false; 	
	}
	
	$code = '<link href="stylesheet/' . $fileName . '" media="screen" rel="Stylesheet" type="text/css"/>' . "\n";
	$currentContent = $smarty->get_template_vars("STYLESHEET");
	$smarty->assign("STYLESHEET", $currentContent . $code);
}

?>