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
function smarty_function_compiledCss($params, LiveCartSmarty $smarty) 
{
    $includedStylesheetTimestamp = $smarty->get_template_vars("INCLUDED_STYLESHEET_TIMESTAMP");
    $includedStylesheetFiles = $smarty->get_template_vars("INCLUDED_STYLESHEET_FILES");
    
    if(isset($params['glue']) && $params['glue'] == 'true')
    {
        $request = $smarty->getApplication()->getRequest();
        $compiledFileName = $request->getControllerName() . '-' . $request->getActionName() . '.css';
        $compiledFilePath = ClassLoader::getRealPath('public.stylesheet.compiled.') .  $compiledFileName;
        $baseDir = ClassLoader::getRealPath('public.stylesheet.');
        
        $compiledFileTimestamp = 0;
        if(!is_file($compiledFilePath) || filemtime($compiledFilePath) < $includedStylesheetTimestamp)
        {
            // compile
            $compiledFileContent = "";
            foreach($includedStylesheetFiles as $cssFile)
            {
                $compiledFileContent .= "\n\n\n/***************************************************\n";
                $compiledFileContent .= " * " . str_replace($baseDir, '', $cssFile) . "\n";
                $compiledFileContent .= " ***************************************************/\n\n";
                
                $compiledFileContent .= file_get_contents($cssFile);
            }
            
            $compiledFileContent = preg_replace('/\.(jpg|png|gif|bmp)/', '.$1?' . time(), $compiledFileContent);
            
            file_put_contents($compiledFilePath, $compiledFileContent);
        }
        $compiledFileTimestamp = filemtime($compiledFilePath);
        
        return '<link href="stylesheet/compiled/' . $compiledFileName . '?' . $compiledFileTimestamp . '" media="screen" rel="Stylesheet" type="text/css"/>';
    }
    else
    {
        $includeString = "";
        $publicPath = ClassLoader::getRealPath('public.');
        
        foreach($includedStylesheetFiles as $cssFile)
        {
            $urlPath = str_replace('\\', '/', str_replace($publicPath, '', $cssFile));
            $includeString .= '<link href="' . $urlPath . '?' . filemtime($cssFile) . '" media="screen" rel="Stylesheet" type="text/css"/>';
        }
        
        return $includeString;
    }
}

?>