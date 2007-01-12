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
function smarty_function_baseUrl($params, $smarty)
{
	return Router::getBaseUrl();
}

?>