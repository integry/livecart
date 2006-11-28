<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_maketext($params, $smarty) 
{	
	return Store::getInstance()->makeText($params['text'], $params['params']);
}

?>