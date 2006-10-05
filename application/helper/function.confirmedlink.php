<?php

/**
 * Helper for creating link with confirm question.
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * 
 */
function smarty_function_confirmedlink($params, $smarty) {

	$question = $params['question'];
	unset($params['question']);
		
	return	
	"javascript: if (confirm('".$question."')) {
		window.location = '".Router::getInstance()->createURL($params)."';
	}";
}

?>