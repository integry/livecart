<?php

/**
 * Generates static page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_pageUrl($params, $smarty)
{	
    if (isset($params['id']))
    {
        $params['data'] = StaticPage::getInstanceById($params['id'])->toArray();        
    }
    
	$urlParams = array('controller' => 'staticPage', 
					   'action' => 'view', 
					   'handle' => $params['data']['handle'], 
                       );

	return Router::getInstance()->createUrl($urlParams);
}

?>