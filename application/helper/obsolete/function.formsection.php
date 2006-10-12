<?php

/**
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Denis Slaveckij <denis@integry.net>
 */
function smarty_function_formsection($params, $smarty) {
	
	$form = $smarty->_tag_stack[0][1]['handler'];
	//todo check num <= count
	if (isSet($params['num']) && is_int($params['num'])) {

		return @$form->getSection($params['num'])->render();
	}
}

?>