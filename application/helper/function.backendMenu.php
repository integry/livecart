<?php

/**
 * Displays backend navigation menu
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_backendMenu($params, Smarty $smarty) 
{
	$locale = Store::getInstance()->getLocaleInstance();
	$controller = $smarty->_tpl_vars['request']['controller'];
	$action = $smarty->_tpl_vars['request']['action'];
	
	if (!$locale->translationManager()->isFileCached('en/menu/menu')
     || !$locale->translationManager()->isFileCached($locale->getlocaleCode() . '/menu/menu'))
	{
        BackendController::rebuildMenuLangFile(); 
    }
	
    // load language file for menu
	$locale->translationManager()->loadCachedFile('en/menu/menu');		
	$locale->translationManager()->loadCachedFile($locale->getlocaleCode() . '/menu/menu');		

	$menuLoader = new MenuLoader();		
	$structure = $menuLoader->getCurrentHierarchy($controller, $action);
	$router = Router::getInstance();
	
	// get translations and generate URL's
	foreach($structure['items'] as &$topValue)
	{
	 	$topValue['title'] = $locale->translator()->translate($topValue['title']);
	    $topValue['url'] = $router->createUrl(array('controller' => $topValue['controller'], 'action' => $topValue['action']));
	
		if (is_array($topValue['items']))
		{
			foreach ($topValue['items'] as &$subValue)
		  	{
			    $subValue['title'] = $locale->translator()->translate($subValue['title']);
			 	$subValue['url'] = $router->createUrl(array('controller' => $subValue['controller'], 'action' => $subValue['action']));
			}		
		}			
	}
	
	$smarty->assign('menuArray', json_encode($structure['items']));
	$smarty->assign('controller', $controller);
	$smarty->assign('action', $action);
	
	return $smarty->display('block/backend/backendMenu.tpl');	
}

?>