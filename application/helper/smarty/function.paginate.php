<?php

/**
 * Generates pagination block
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_paginate($params, LiveCartSmarty $smarty)
{
	$interval = 2;

	// determine which page numbers will be displayed
	$count = ceil($params['count'] / $params['perPage']);

	$pages = range(max(1, $params['current'] - $interval), min($count, $params['current'] + $interval));

	if (array_search(1, $pages) === false)
	{
		array_unshift($pages, 1);
	}

	if (array_search($count, $pages) === false)
	{
		$pages[] = $count;
	}

	// check for any 1-page sized interval breaks
	$pr = 0;
	foreach ($pages as $k)
	{
		if ($k - 2 == $pr)
		{
			$pages[] = $k - 1;
		}

		$pr = $k;
	}
	sort($pages);

	// generate output
	$out = array();

	$application = $smarty->getApplication();

	// get variable to replace - _page_ if defined, otherwise 0
	$replace = strpos($params['url'], '_000_') ? '_000_' : 0;

	$render = array();
	if ($params['current'] > 1)
	{
		$urls['previous'] = str_replace($replace, $params['current'] - 1, $params['url']);
	}

	foreach ($pages as $k)
	{
		$urls[$k] = str_replace($replace, $k, $params['url']);
	}

	if ($params['current'] < $count)
	{
		$urls['next'] = str_replace($replace, $params['current'] + 1, $params['url']);
	}

	$smarty->assign('urls', $urls);
	$smarty->assign('pages', $pages);
	$smarty->assign('current', $params['current']);
	return $smarty->fetch($smarty->getApplication()->getRenderer()->getTemplatePath('block/box/paginate.tpl'));

	return implode(' ', $out);
}

?>