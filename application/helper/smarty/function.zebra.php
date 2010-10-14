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
	static $internalCounter = 0;

	if (!isset($smarty->_foreach[$loop]))
	{
		$loop = 'internal';
		$total = 0;
		$iteration = isset($smarty->zebra[$loop]) ? $smarty->zebra[$loop] + 1 : 1;
	}
	else
	{
		$loop = $params['loop'];
		$total = $smarty->_foreach[$loop]['total'];
		$iteration = $smarty->_foreach[$loop]['iteration'];
	}

	if (empty($iteration) || !isset($smarty->zebra[$loop]))
	{
		$smarty->zebra[$loop] = 0;
		$iteration = 1;
	}

	$firstOrLast = '';
	if (1 == $iteration)
	{
		$firstOrLast = ' first';
	}
	if ($total == $iteration)
	{
		$firstOrLast = ' last';
	}

	if (!isset($params['stop']))
	{
		++$smarty->zebra[$loop];
	}

	return 'zebra ' . ($smarty->zebra[$loop] % 2 ? 'even' : 'odd') . $firstOrLast;
}

?>