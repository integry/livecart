<?php

/**
 * Helper for hiding hints {@see http://javascript.geniusbug.com} For more information
 * Used together with {@see smarty_function_hintshow}
 *
 * @param array $params 
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_hinthide($params, $smarty) {

	return "movingHint.show(); simpleHint.show();";	  		
}

?>