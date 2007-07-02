<?php

/**
 * Displays ActiveGrid table
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_activeGrid($params, Smarty $smarty) 
{
    if (!isset($params['rowCount']) || !$params['rowCount'])
    {
		$params['rowCount'] = 15;	
	}
	
	foreach ($params as $key => $value)
    {
        $smarty->assign($key, $value);
    }
    
    $filtersString = '';
    if (isset($params['filters']) && is_array($params['filters']))
    {
        foreach($params['filters'] as $key => $value)
        {
            $filtersString .= "filters[$key]=$value&";
        }
        
        $smarty->assign('filters', "?$filtersString");
    }
    
    $smarty->assign('url', Router::getInstance()->createUrl(array('controller' => $params['controller'], 'action' => $params['action'])));
    
	return $smarty->display('block/activeGrid/gridTable.tpl');
}

?>