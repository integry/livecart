<?php

/**
 * Displays backend language selection menu
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_backendLangMenu($params, LiveCartSmarty $smarty)
{
  	if (!$smarty->getApplication()->getLanguageArray())
  	{
		return false;
	}

	$smarty->assign('currentLang', Language::getInstanceByID($smarty->getApplication()->getLocaleCode())->toArray());
	$smarty->assign('returnRoute', base64_encode($smarty->getApplication()->getRouter()->getRequestedRoute()));
	return $smarty->display('block/backend/langMenu.tpl');
}

?>