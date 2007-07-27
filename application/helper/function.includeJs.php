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
    $includedJavascriptTimestamp = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_TIMESTAMP");
    if(!($includedJavascriptFiles = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_FILES")))
    {
       $includedJavascriptFiles = array();
    }
    
	// fix slashes
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $params['file']);
    $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
    $filePath = ClassLoader::getRealPath('public.javascript.') .  $fileName;
    
    if(!in_array($filePath, $includedJavascriptFiles) && is_file($filePath))
    {        
        $fileMTime = filemtime($filePath);
        if($fileMTime > (int)$includedJavascriptTimestamp)
        {
            $smarty->assign("INCLUDED_JAVASCRIPT_TIMESTAMP", $fileMTime);
        }
        
        if(isset($params['front']))
        {
            array_unshift($includedJavascriptFiles, $filePath);
        }
        else
        {
            array_push($includedJavascriptFiles, $filePath);
        }
        
        $smarty->assign("INCLUDED_JAVASCRIPT_FILES", $includedJavascriptFiles);
    }
}
?>