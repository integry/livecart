<?php
/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Integry Systems
 */
function smarty_function_includeJs($params, LiveCartSmarty $smarty) 
{
	// fix slashes
    $fileName = str_replace('\\', '/', $params['file']);
    $filePath = ClassLoader::getRealPath('public.javascript.') . str_replace('/', DIRECTORY_SEPARATOR, $fileName);
	$currentContent = $smarty->get_template_vars("JAVASCRIPT");
	
    // Check to see if it is already included
	if (strpos($currentContent, $fileName) === false && is_file($filePath))
	{
        $mtime = filemtime($filePath);
		$code = '<script src="javascript/' . $fileName . '?' . $mtime . '" type="text/javascript"></script>' . "\n";

		if(isset($params['force']))
		{
		    return $code;
		}
		else
		{
		   $smarty->assign("JAVASCRIPT", $currentContent . $code);
		}
	}
}

?>