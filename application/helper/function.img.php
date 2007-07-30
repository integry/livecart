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
function smarty_function_img($params, LiveCartSmarty $smarty) 
{
    if(isset($params['src']) && substr($params['src'], 0, 6) == 'image/')
    {
        $imageTimestamp = filemtime(ClassLoader::getRealPath('public.') . str_replace('/',DIRECTORY_SEPARATOR, $params['src']));
        $params['src'] .= '?' . $imageTimestamp;
    }
    
    $content = "\n<img ";
    foreach($params as $name => $value)
    {
        $content .= $name . '="' . $value . '" ';
    }
    $content .= "/>\n";
    
    return $content;
}
?>