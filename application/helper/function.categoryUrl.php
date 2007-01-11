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
	
	return $router->createUrl(array('controller' => 'category', 'action' => 'index', 'id' => $category['ID']));
}

?>