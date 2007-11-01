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
function smarty_function_compiledJs($params, LiveCartSmarty $smarty) 
{
    $includedJavascriptTimestamp = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_TIMESTAMP");
    $includedJavascriptFiles = $smarty->get_template_vars("INCLUDED_JAVASCRIPT_FILES");
    
    if(isset($params['glue']) && $params['glue'] == 'true' && !$smarty->getApplication()->isDevMode())
    {
        $request = $smarty->getApplication()->getRequest();
        $compiledFileName = $request->getControllerName() . '-' . $request->getActionName() . '.js';
        $compiledFilePath = ClassLoader::getRealPath('public.cache.javascript.') .  $compiledFileName;
        $baseDir = ClassLoader::getRealPath('public.javascript.');
        
        $compiledFileTimestamp = 0;
        if(!is_file($compiledFilePath) || filemtime($compiledFilePath) < $includedJavascriptTimestamp)
        {
            if(!is_dir(ClassLoader::getRealPath('public.cache.javascript'))) 
            {
                mkdir(ClassLoader::getRealPath('public.cache.javascript'), 0777, true);
            }
            
            // compile
            $compiledFileContent = "";
            $compiledFilesList = array();
            foreach($includedJavascriptFiles as $jsFile => $fileName)
            {
                $compiledFileContent .= "\n\n\n/***************************************************\n";
                $compiledFileContent .= " * " . str_replace($baseDir, '', $jsFile) . "\n";
                $compiledFileContent .= " ***************************************************/\n\n";
                
                $compiledFileContent .= file_get_contents($jsFile); 
                $compiledFilesList[] = basename($jsFile);
            }
            
//            $compiledFileContent .= "\n\n console.info('All javascript files were glued together successfully in following order:\\n  " 
//                                 . implode("\\n  ", $compiledFilesList) 
//                                 . "')\n";
                                
            file_put_contents($compiledFilePath, $compiledFileContent);
        }
        
        $compiledFileTimestamp = filemtime($compiledFilePath);
        
        return '<script src="cache/javascript/' . $compiledFileName . '?' . $compiledFileTimestamp . '" type="text/javascript"></script>';
    }
    else if ($includedJavascriptFiles)
    {
        $includeString = "";
        $publicPath = ClassLoader::getRealPath('public.');
        foreach($includedJavascriptFiles as $path => $jsFile)
        {
            $urlPath = str_replace('\\', '/', str_replace($publicPath, '', $jsFile));
            $includeString .= '<script src="' .$urlPath . '?' . filemtime($path) . '" type="text/javascript"></script>' . "\n";
        }
        
        return $includeString;
    }
}

?>
