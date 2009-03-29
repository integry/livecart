<?php

ClassLoader::import('application.helper.MenuLoader');
ClassLoader::import('application.helper.AccessStringParser');

/**
 * Displays backend navigation menu
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_backendMenu($params, LiveCartSmarty $smarty)
{
	$locale = $smarty->getApplication()->getLocale();
	$controller = $smarty->_tpl_vars['request']['controller'];
	$action = $smarty->_tpl_vars['request']['action'];

	// load language file for menu
	$locale->translationManager()->loadFile('backend/menu');

	$menuLoader = new MenuLoader($smarty->getApplication());
	$structure = $menuLoader->getCurrentHierarchy($controller, $action);
	$router = $smarty->getApplication()->getRouter();

	// get translations and generate URL's
	$items = array();
	foreach($structure['items'] as $topValue)
	{
	 	if(!empty($topValue['role']) && !AccessStringParser::run($topValue['role']))
	 	{
	 		continue;
	 	}

	 	$filteredValue = array();
		foreach (array('title', 'descr') as $field)
		{
			$filteredValue[$field] = $smarty->branding($locale->translator()->translate($topValue[$field]));
		}

		$filteredValue['controller'] = $topValue['controller'];
		$filteredValue['action'] = $topValue['action'];
		$filteredValue['icon'] = $topValue['icon'];

		if(!empty($topValue['controller']))
		{
			$filteredValue['url'] = $router->createUrl(array('controller' => $topValue['controller'], 'action' => $topValue['action']), true);
		}

		if (is_array($topValue['items']))
		{
			$subItems = array();
			foreach ($topValue['items'] as &$subValue)
		  	{
		  		if(!empty($subValue['role']) && !AccessStringParser::run($subValue['role']))
		  		{
			 		continue;
		  		}

		  		$filteredSubValue = array();
				foreach (array('title', 'descr') as $field)
				{
					$filteredSubValue[$field] = $smarty->branding($locale->translator()->translate($subValue[$field]));
				}

				$filteredSubValue['url'] = $router->createUrl(array('controller' => $subValue['controller'], 'action' => $subValue['action']), true);
				$filteredSubValue['controller'] = $subValue['controller'];
				$filteredSubValue['action'] = $subValue['action'];
				$filteredSubValue['icon'] = $subValue['icon'];

				$subItems[] = $filteredSubValue;
			}

			if(count($subItems) > 0)
			{
				$filteredValue['items'] = $subItems;
			}
		}

		$items[] = $filteredValue;
	}

	$smarty->assign('menuArray', json_encode($items));
	$smarty->assign('controller', $controller);
	$smarty->assign('action', $action);

	return $smarty->display('block/backend/backendMenu.tpl');
}

?>