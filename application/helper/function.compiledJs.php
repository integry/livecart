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
function smarty_function_compiledJs($params, LiveCartSmarty $smarty) 
{
    $includedJavascriptTimestamp = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_TIMESTAMP");
    $includedJavascriptFiles = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_FILES");
    
    if(isset($params['glue']) && $params['glue'] == 'true')
    {
        $request = $smarty->getApplication()->getRequest();
        $compiledFileName = $request->getControllerName() . '-' . $request->getActionName() . '.js';
        $compiledFilePath = ClassLoader::getRealPath('public.javascript.compiled.') .  $compiledFileName;
        $baseDir = ClassLoader::getRealPath('public.javascript.');
        
        $compiledFileTimestamp = 0;
        if(!is_file($compiledFilePath) || filemtime($compiledFilePath) < $includedJavascriptTimestamp)
        {
            // compile
            $compiledFileContent = "";
            $compiledFilesList = array();
            foreach($includedJavascriptFiles as $jsFile)
            {
                $compiledFileContent .= "\n\n\n/***************************************************\n";
                $compiledFileContent .= " * " . str_replace($baseDir, '', $jsFile) . "\n";
                $compiledFileContent .= " ***************************************************/\n\n";
                
                $compiledFileContent .= file_get_contents($jsFile); 
                $compiledFilesList[] = basename($jsFile);
            }
            
            $compiledFileContent .= "\n\n console.info('All javascript files were glued together successfully in following order:\\n  " 
                                 . implode("\\n  ", $compiledFilesList) 
                                 . "')\n";
                                 
            file_put_contents($compiledFilePath, $compiledFileContent);
        }
        
        $compiledFileTimestamp = filemtime($compiledFilePath);
        
        return '<script src="javascript/compiled/' . $compiledFileName . '?' . $compiledFileTimestamp . '" type="text/javascript"></script>';
    }
    else
    {
        $includeString = "";
        $publicPath = ClassLoader::getRealPath('public.');
        foreach($includedJavascriptFiles as $jsFile)
        {
            $urlPath = str_replace('\\', '/', str_replace($publicPath, '', $jsFile));
            $includeString .= '<script src="' .$urlPath . '?' . filemtime($jsFile) . '" type="text/javascript"></script>' . "\n";
        }
        
        return $includeString;
    }
}

?>