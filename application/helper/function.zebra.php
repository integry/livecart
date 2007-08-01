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
	
	if (0 == $smarty->_foreach[$params['loop']]['iteration'] || !isset($smarty->zebra[$params['loop']]))
	{
		$smarty->zebra[$params['loop']] = 0;
	}
	
	return ' class="' . (++$smarty->zebra[$params['loop']] % 2 ? 'even' : 'odd') .'"';
}

?>