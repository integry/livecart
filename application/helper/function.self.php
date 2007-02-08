<?php

/**
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_self($params, Smarty $smarty) 
{
	return $_SERVER['REQUEST_URI'];	
}

?>