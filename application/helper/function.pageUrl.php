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
function smarty_function_pageUrl($params, LiveCartSmarty $smarty)
{	
    if (isset($params['id']))
    {
        $params['data'] = StaticPage::getInstanceById($params['id'], StaticPage::LOAD_DATA)->toArray();        
    }
    
	$urlParams = array('controller' => 'staticPage', 
					   'action' => 'view', 
					   'handle' => $params['data']['handle'], 
                       );

	return $smarty->getApplication()->getRouter()->createUrl($urlParams);
}

?>