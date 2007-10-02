<?php

ClassLoader::import('application.helper.CreateHandleString');

/**
 * Generates category page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems 
 */
function smarty_function_categoryUrl($params, LiveCartSmarty $smarty)
{		
    return createCategoryUrl($params, $smarty->getApplication());
}

function createCategoryUrl($params, LiveCart $application)
{
	$category = $params['data'];	
	
	// get full category path
	$parts = array();
	$parts[] = createHandleString($category['name_lang']);
	
    if (!isset($category['parent']))
	{
        $category['parent'] = 0;    
    }
	
    $current = $category['parent'];	
	
    while ($current > 1)
	{
	  	$cat = Category::getInstanceByID($current, true);
	  	$parts[] = createHandleString($cat->getValueByLang('name', $application->getLocaleCode()));
	  	$current = $cat->parentNode->get()->getID();
	}
	
    $parts = array_reverse($parts);	
	
    $handle = implode('-', $parts);
	
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
		$handle = '-';
	}

	if (!isset($category['ID']))
	{
		$category['ID'] = 1;
	}
		
	$urlParams = array('controller' => 'category', 
					   'action' => 'index', 
					   'cathandle' => $handle, 
					   'id' => $category['ID'],
                       );
	
	if (!empty($params['query']))
	{
        $urlParams['query'] = $params['query'];    
    }    				  
                       
	if (!empty($params['page']))
	{
        $urlParams['page'] = $params['page'];    
    }    				  

	if ($filters)
	{
	  	$urlParams['filters'] = implode(',', $filters);
	}

	return $application->getRouter()->createUrl($urlParams);    
}

function filterHandle($filter)
{
	if (is_object($filter))
	{
        $filter = $filter->toArray();
    }
    return (isset($filter['FilterGroup']) ? $filter['FilterGroup']['SpecField']['handle'] . '.' : '') . $filter['handle'] . '-' . $filter['ID'];	  
}

?>