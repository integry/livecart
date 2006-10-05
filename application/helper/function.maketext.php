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
function smarty_function_maketext($params, $smarty) {
	
	$locale = Locale::getCurrentLocale();		
	return	$locale->makeText($params['text'], $params['params']);
}

?>