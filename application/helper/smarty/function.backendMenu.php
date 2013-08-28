<?php


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
function smarty_function_backendMenu($params, Smarty_Internal_Template $smarty)
{
	$smarty = $smarty->smarty;
	$locale = $smarty->getApplication()->getLocale();
	$request = $smarty->getApplication()->getRequest();
	$controller = $request->gget('controller');
	$action = $request->gget('action');

	// load language file for menu
	$locale->translationManager()->loadFile('backend/menu');

	$menuLoader = new MenuLoader($smarty->getApplication(), $params['menu']);
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

		$filteredValue['controller'] = isset($topValue['controller']) ? $topValue['controller'] : '';
		$filteredValue['action'] = isset($topValue['action']) ? $topValue['action'] : '';
		$filteredValue['icon'] = isset($topValue['icon']) ? $topValue['icon'] : '';

		if(!empty($topValue['controller']))
		{
			$filteredValue['url'] = $router->createUrl(array('controller' => $topValue['controller'], 'action' => (isset($topValue['action']) ? $topValue['action'] : null)), true);
		}

		if (isset($topValue['items']) && is_array($topValue['items']))
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

				$filteredSubValue['url'] = $router->createUrl(array('controller' => $subValue['controller'], 'action' => (isset($subValue['action']) ? $subValue['action'] : null)), true);
				if (!empty($subValue['query']))
				{
					$filteredSubValue['url'] .= '?' . $subValue['query'];
				}

				$filteredSubValue['controller'] = $subValue['controller'];
				$filteredSubValue['action'] = isset($subValue['action']) ? $subValue['action'] : '';
				$filteredSubValue['icon'] = isset($subValue['icon']) ? $subValue['icon'] : '';

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