<?php

/**
 * "Zebra" style table row formatting
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_zebra($params, LiveCartSmarty $smarty)
{
	$loop = $params['loop'];

	if (!isset($smarty->_foreach[$loop]))
	{
		return false;
	}

	if (0 == $smarty->_foreach[$loop]['iteration'] || !isset($smarty->zebra[$loop]))
	{
		$smarty->zebra[$loop] = 0;
	}

	$firstOrLast = '';
	if (1 == $smarty->_foreach[$loop]['iteration'])
	{
		$firstOrLast = ' first';
	}
	if ($smarty->_foreach[$loop]['total'] == $smarty->_foreach[$loop]['iteration'])
	{
		$firstOrLast = ' last';
	}

	if (!isset($params['stop']))
	{
		++$smarty->zebra[$loop];
	}

	return ($smarty->zebra[$loop] % 2 ? 'even' : 'odd') . $firstOrLast;
}

?>