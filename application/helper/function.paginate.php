<?php

/**
 * Generates pagination block
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
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
	
	if ($params['current'] > 1)
	{
		$out[] = '<a class="page previous" href="' . str_replace('_page_', $params['current'] - 1, $params['url']) . '">' . $application->translate('_previous') . '</a>';
	}
	
	$pr = 0;
    foreach ($pages as $k)
	{
		if ($pr < $k - 1)
		{
            $out[] = '...';
        }
        
        if ($k != $params['current'])
		{
			$out[] = '<a class="page" href="' . str_replace('_page_', $k, $params['url']) . '">' . $k . '</a>';			
		}
		else
		{
			$out[] = '<span class="page currentPage">' . $k . '</span>';					
		}
		
		$pr = $k;
	}

	if ($params['current'] < $count)
	{
		$out[] = '<a class="page next" href="' . str_replace('_page_', $params['current'] + 1, $params['url']) . '">' . $application->translate('_next') . '</a>';
	}

	return implode(' ', $out);
}

?>