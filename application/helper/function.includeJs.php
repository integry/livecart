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
	
	// fix slashes
	$fileName = str_replace(chr(92),'/', $fileName);
	
	// check if file exists
	if (!file_exists(ClassLoader::getRealPath('public.javascript.') . $fileName))
	{
		return false; 	
	}
	
	$currentContent = $smarty->get_template_vars("JAVASCRIPT");
	if (strpos($currentContent, $fileName) === false)
	{
		$code = '<script src="javascript/' . $fileName . '" type="text/javascript"></script>' . "\n";
		$smarty->assign("JAVASCRIPT", $currentContent . $code);
	}
}

?>