<?php

/**
 * Generates static page title
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_pageName($params, LiveCartSmarty $smarty)
{	
    if (!isset($params['id']))
    {
        return '<span style="color: red; font-weight: bold; font-size: larger;">No static page ID provided</span>';
    }
    
    $page = StaticPage::getInstanceById($params['id'])->toArray();
    
    return $page['title_lang'];
}

?>