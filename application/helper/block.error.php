<?php

/**
 * Form field error message block
 *
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_block_form($params, $content, $smarty, &$repeat) {
	
	$formParams = $smarty->_tag_stack[0][1];
	$validator = $formParams['validator'];
	
	$errorMsg = "";
	
	return $content;
}

?>