<?php
/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_includeJs($params, LiveCartSmarty $smarty) 
{
    // fix slashes
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $params['file']);
    $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
    $filePath = ClassLoader::getRealPath('public.javascript.') .  $fileName;
        
    if(!is_file($filePath)) return;
    
    if(isset($params['inline']) && $params['inline'] == 'true')
    {
        return '<script src="javascript/' . str_replace(DIRECTORY_SEPARATOR, '/', $fileName) . '?' . filemtime($filePath) . '" type="text/javascript"></script>' . "\n";
    }
    else
    {
        $includedJavascriptTimestamp = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_TIMESTAMP");
        if(!($includedJavascriptFiles = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_FILES")))
        {
           $includedJavascriptFiles = array();
        }
        
        if(in_array($filePath, $includedJavascriptFiles))
        {
			if (!isset($params['front']))
			{
				return false;	
			}
			else
			{
				unset($includedJavascriptFiles[array_search($filePath, $includedJavascriptFiles)]);
			}			
		}
        
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