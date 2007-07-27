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
    // fix slashes
    $fileName = str_replace('\\', '/', $params['file']);
    $filePath = ClassLoader::getRealPath('public.stylesheet.') . str_replace('/', DIRECTORY_SEPARATOR, $fileName);
    $currentContent = $smarty->get_template_vars("STYLESHEET");
	
    // Check to see if it is already included
    if (strpos($currentContent, $fileName) === false && is_file($filePath))
    {
        $mtime = filemtime($filePath);
    	$code = '<link href="stylesheet/' . $fileName . '?' . $mtime .  '" media="screen" rel="Stylesheet" type="text/css"/>' . "\n";
    	
        if(isset($params['force']))
        {
            return $code;
        }
        else
        {
    	   $smarty->assign("STYLESHEET", $currentContent . $code);
        }
    }
}

?>