<?php

/**
 * Inserts a base URL string
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_helpUrl($params, $smarty)
{
	$router = Router::getInstance();
	return $router->createUrl(array('controller' => 'backend.help', 'action' => 'view', 'id' => $params['help']));
}

?>