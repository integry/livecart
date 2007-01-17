<?php

/**
 * Translates interface text to current locale language
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_categoryUrl($params, Smarty $smarty)
{	
	$category = $params['data'];	
	$router = Router::getInstance();
	
	// get full category path
	$parts = array();
	$parts[] = $category['handle'];
	$current = $category['parent'];
	while ($current > 1)
	{
	  	$cat = Category::getInstanceByID($current, true);
	  	$parts[] = $cat->handle->get();
	  	$current = $cat->category->get()->getID();
	}
	$parts = array_reverse($parts);	
	$handle = implode('.', $parts);
	
	$filters = array();

	// remove filter (expand search)
	if (isset($params['removeFilter']))
	{
	  	foreach ($params['filters'] as $key => $filter)
	  	{
		    if ($filter['ID'] == $params['removeFilter']['ID'])
		    {
				unset($params['filters'][$key]);
			}
		}
	}

	// get filters
	if (isset($params['filters']))
	{
		foreach ($params['filters'] as $filter)
		{
		  	$filters[] = filterHandle($filter);
		}
	}

	// apply new filter (narrow search)
	if (isset($params['addFilter']))
	{
	  	$filters[] = filterHandle($params['addFilter']);
	}	

	if (empty($handle))
	{
		$handle = '.';
	}

	$urlParams = array('controller' => 'category', 
					   'action' => 'index', 
					   'cathandle' => $handle, 
					   'id' => $category['ID']);
					   
	if ($filters)
	{
	  	$urlParams['filters'] = implode(',', $filters);
	}

	return $router->createUrl($urlParams);
}

function filterHandle($filter)
{
	return $filter['FilterGroup']['SpecField']['handle'] . '.' . $filter['handle'] . '-' . $filter['ID'];	  
}

?>