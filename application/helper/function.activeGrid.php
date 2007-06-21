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
    foreach ($params as $key => $value)
    {
        $smarty->assign($key, $value);
    }

    $smarty->assign('url', Router::getInstance()->createUrl(array('controller' => $params['controller'], 'action' => $params['action'])));
    
	return $smarty->display('block/activeGrid/gridTable.tpl');
}

?>