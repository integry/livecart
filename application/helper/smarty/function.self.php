<?php

/**
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_self($params, Smarty_Internal_Template $smarty)
{
	return $_SERVER['REQUEST_URI'];
}

?>