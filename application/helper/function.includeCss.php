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
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $params['file']);
    $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
    $filePath = ClassLoader::getRealPath('public.stylesheet.') .  $fileName;
    
    if(!is_file($filePath)) return;
    
    if(isset($params['inline']) && $params['inline'] == 'true')
    {
        return '<link href="stylesheet/' . str_replace(DIRECTORY_SEPARATOR, '/', $fileName) . '?' . filemtime($filePath) . '"" media="screen" rel="Stylesheet" type="text/css" />' . "\n";
    }
    else
    {
        $includedStylesheetTimestamp = $smarty->get_template_vars("INCLUDED_STYLESHEET_TIMESTAMP");
        if(!($includedStylesheetFiles = $smarty->get_template_vars("INCLUDED_STYLESHEET_FILES")))
        {
           $includedStylesheetFiles = array();
        }
        
        if(isset($params['front']) && in_array($filePath, $includedStylesheetFiles))
        {
            unset($includedStylesheetFiles[array_search($filePath, $includedStylesheetFiles)]);
        }
        
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