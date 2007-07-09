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
function smarty_function_maketext($params, LiveCartSmarty $smarty) 
{	
	return $smarty->getApplication()->makeText($params['text'], $params['params']);
}

?>