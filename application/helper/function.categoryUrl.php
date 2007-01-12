<?php

/**
 * Translates interface text to current locale language
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_categoryUrl($params, Smarty $smarty)
{
	$category = $params['data'];	
	$router = Router::getInstance();
	
	// get full category path
	$parts = array();
	$parts[] = $category['handle'];
	$current = $category['parent'];
	while ($current != 1)
	{
	  	$cat = Category::getInstanceByID($current, true);
	  	$parts[] = $cat->handle->get();
	  	$current = $cat->category->get()->getID();
	}
	$parts = array_reverse($parts);
	
	$handle = implode('.', $parts) . '-' . $category['ID'];
	
	return $router->createUrl(array('controller' => 'category', 'action' => 'index', 'id' => $handle));
}

?>