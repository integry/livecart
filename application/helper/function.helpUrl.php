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
function smarty_function_helpUrl($params, LiveCartSmarty $smarty)
{
	return $smarty->getApplication()->getRouter()->createUrl(array('controller' => 'help', 'action' => 'view', 'id' => $params['help']));
}

?>