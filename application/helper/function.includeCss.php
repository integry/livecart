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
    $includedStylesheetTimestamp = $smarty->get_template_vars("INCLUDED_STYLESHEET_TIMESTAMP");
    if(!($includedStylesheetFiles = $smarty->get_template_vars("INCLUDED_STYLESHEET_FILES")))
    {
       $includedStylesheetFiles = array();
    }
    
    // fix slashes
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $params['file']);
    $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
    $filePath = ClassLoader::getRealPath('public.stylesheet.') .  $fileName;
    
    if(!in_array($filePath, $includedStylesheetFiles) && is_file($filePath))
    {        
        $fileMTime = filemtime($filePath);
        if($fileMTime > (int)$includedStylesheetTimestamp)
        {
            $smarty->assign("INCLUDED_STYLESHEET_TIMESTAMP", $fileMTime);
            
        }
        
        if(isset($params['front']))
        {
            array_unshift($includedStylesheetFiles, $filePath);
        }
        else
        {
            array_push($includedStylesheetFiles, $filePath);
        }
        
        $smarty->assign("INCLUDED_STYLESHEET_FILES", $includedStylesheetFiles);
    }
}
?>