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
    
//    ClassLoader::import('application.helper.AccessStringParser');
    
	$locale = Store::getInstance()->getLocaleInstance();
	$controller = $smarty->_tpl_vars['request']['controller'];
	$action = $smarty->_tpl_vars['request']['action'];
	
    // load language file for menu
	$locale->translationManager()->loadFile('backend/menu');		

	$menuLoader = new MenuLoader();		
	$structure = $menuLoader->getCurrentHierarchy($controller, $action);
	$router = Router::getInstance();
	
	// get translations and generate URL's
	foreach($structure['items'] as $topNo => &$topValue)
	{
	 	if(!empty($topValue['role']) && !AccessStringParser::run($topValue['role'])) 
	 	{
	 	    unset($structure['items'][$topNo]);
	 	    continue;
	 	}
	    
	    $topValue['title'] = $locale->translator()->translate($topValue['title']);
	 		 	
	    if(!empty($topValue['controller']))
	    {
	        $topValue['url'] = $router->createUrl(array('controller' => $topValue['controller'], 'action' => $topValue['action']));
	    }
	    
		if (is_array($topValue['items']))
		{
			foreach ($topValue['items'] as $subNo => &$subValue)
		  	{
		  	    if(!empty($subValue['role']) && !AccessStringParser::run($subValue['role'])) 
		  	    {
			 	    unset($structure['items'][$topNo]['items'][$subNo]);
			 	    continue;
		  	    }
		  	    
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