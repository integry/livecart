<?php

/**
 * "Zebra" style table row formatting
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_zebra($params, LiveCartSmarty $smarty) 
{
	if (!isset($smarty->_foreach[$params['loop']]))
	{
        return false;
    }
	
	$index = $smarty->_foreach[$params['loop']]['iteration'];
	
	return ' class="' . ($index % 2 ? 'even' : 'odd') .'"';
}

?>