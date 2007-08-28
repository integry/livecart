<?php

/**
 * Inserts a base URL string
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 */
function smarty_function_baseUrl($params, LiveCartSmarty $smarty)
{
	return $smarty->getApplication()->getRouter()->getBaseUrl();
}

?>