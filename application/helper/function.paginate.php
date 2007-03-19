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
		$out[] = '<a href="' . str_replace('_page_', $params['current'] - 1, $params['url']) . '">' . $store->translate('_previous') . '</a>';
	}
	
	for ($k = 1; $k <= $pages; $k++)
	{
		if ($k != $params['current'])
		{
			$out[] = '<a href="' . str_replace('_page_', $k, $params['url']) . '">' . $k . '</a>';			
		}
		else
		{
			$out[] = '<span class="current">' . $k . '</span>';					
		}
	}

	if ($params['current'] < $pages)
	{
		$out[] = '<a href="' . str_replace('_page_', $params['current'] + 1, $params['url']) . '">' . $store->translate('_next') . '</a>';
	}

	return implode(' | ', $out);
}

?>