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
	
	// load language file for menu
	$locale->translationManager()->loadCachedFile('en/menu/menu');		
	$locale->translationManager()->loadCachedFile($locale->getlocaleCode() . '/menu/menu');		

	$menuLoader = new MenuLoader();		
	$structure = $menuLoader->getCurrentHierarchy($controller, $action);

	$index = 0;

	foreach($structure['items'] as $topIndex => $topValue)
	{
	    if ($controller == $topValue['controller'] && $action == $topValue['action'])
	    {
		  	$index = $topIndex;
		  	break;
		}
		else if ($controller == $topValue['controller'])
		{
		  	$index = $topIndex;
		}		

	  	$match = false;
		if (is_array($topValue['items']))
		{
			foreach ($topValue['items'] as $subIndex => $subValue)
		  	{
			    if ($controller == $subValue['controller'] && $action == $subValue['action'])
			    {
				  	$index = $topIndex;
				  	$match = true;
				  	break;
				}	
				else if ($controller == $subValue['controller'])
				{
				  	$index = $topIndex;
				}		
			}
			
			if ($match)
			{
			  	break;
			}
		}			
	}
	
	$smarty->assign('items', $structure['items']);
	$smarty->assign('itemIndex', $index);
	return $smarty->display('block/backendMenu.tpl');	
}

?>