<?php

/**
 * Displays backend language selection menu
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_backendLangMenu($params, Smarty $smarty) 
{
  	$router = Router::getInstance();
  	$smarty->assign('returnRoute', base64_encode($router->getRequestedRoute()));
	return $smarty->display('block/backend/langMenu.tpl');
}

?>