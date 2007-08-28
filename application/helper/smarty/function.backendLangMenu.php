<?php

/**
 * Displays backend language selection menu
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper.smarty
 */
function smarty_function_backendLangMenu($params, LiveCartSmarty $smarty) 
{
  	$smarty->assign('returnRoute', base64_encode($smarty->getApplication()->getRouter()->getRequestedRoute()));
	return $smarty->display('block/backend/langMenu.tpl');
}

?>