<?php

/**
 * Formats "zebra style" table rows (each second row is highlighted)
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_zebraRow($params, Smarty $smarty) 
{
	$f = $smarty->_foreach;
	$currentForeach = array_pop($f);
	if ($currentForeach['iteration'] % 2 == 1)
	{
		return ' class="altrow"';
	}
}

?>