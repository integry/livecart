<?php

/**
 * Inserts a base URL string
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_paginate($params, $smarty)
{
	$pages = ceil($params['count'] / $params['perPage']);

	$out = array();
	
	$store = Store::getInstance();
	
	if ($params['current'] > 1)
	{
		$out[] = '<a href="' . $params['url'] . ($params['current'] - 1) . '">' . $store->translate('_previous') . '</a>';
	}
	
	for ($k = 1; $k <= $pages; $k++)
	{
		if ($k != $params['current'])
		{
			$out[] = '<a href="' . $params['url'] . $k . '">' . $k . '</a>';			
		}
		else
		{
			$out[] = '<span class="current">' . $k . '</span>';					
		}
	}

	if ($params['current'] < $params['count'])
	{
		$out[] = '<a href="' . $params['url'] . ($params['current'] + 1) . '">' . $store->translate('_next') . '</a>';
	}

	return implode(' | ', $out);
}

?>