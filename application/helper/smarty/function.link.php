<?php

ClassLoader::import("framework.request.Router");

/**
 * Smarty helper function for creating hyperlinks in application.
 * As the format of application part addresing migth vary, links should be created
 * by using this helper method. When the addressing schema changes, all links
 * will be regenerated
 * 
 * "query" is a special paramater, that will be appended to a generated link as "?query"
 * Example: {link controller=category action=remove id=33 query="language=$lang&returnto=someurl"}
 *
 * @param array $params List of parameters passed to a function
 * @param Smarty $smarty Smarty instance
 * @return string Smarty function resut (formatted link)
 * 
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_link($params, LiveCartSmarty $smarty)
{
	$router = $smarty->getApplication()->getRouter();
	
	// should full URL be generated?
    if (isset($params['url']))
	{
		unset($params['url']);
		$fullUrl = true;
	}
	else
	{
		$fullUrl = false;		
	}
	
	// replace & with &amp;
	if (isset($params['nohtml']))
	{
        unset($params['nohtml']);
        $router->setVariableSeparator('&');
    }
    else
    {
        $router->setVariableSeparator('&amp;');
    }

	try
	{
		if (isset($params['route']))
		{
			$result = $router->createUrlFromRoute($params['route']);
		}
		else
		{			
			$result = $router->createURL($params, true);			
		}
	}
	catch(RouterException $e)
	{
		return "INVALID_LINK";
	}

	if ($fullUrl)
	{
		$result = $router->createFullUrl($result);
	}

	return $result;
}

?>