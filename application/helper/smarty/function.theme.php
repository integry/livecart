<?php

/**
 * Inserts a base URL string
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_theme($params, Smarty_Internal_Template $smarty)
{
	return $smarty->getApplication()->getTheme();
}

?>