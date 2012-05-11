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
function smarty_function_link($params, Smarty_Internal_Template $smarty)
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

	$queryParams = $params;
	unset($queryParams['route'], $queryParams['nohtml'], $queryParams['self'], $queryParams['controller'], $queryParams['action'], $queryParams['id'], $queryParams['query']);
	if (!isset($params['query']))
	{
		$params['query'] = $queryParams;
	}

	if (isset($params['self']))
	{
		$result = $_SERVER['REQUEST_URI'];

		foreach ($queryParams as $param => $value)
		{
			$result = $router->setUrlQueryParam($result, $param, $value);
		}
	}
	else
	{
		try
		{
			if (!empty($params['route']))
			{
				$result = $router->createUrlFromRoute($params['route'], false);
			}
			else
			{
				unset($params['route']);
				$result = $router->createURL($params, false);
			}
		}
		catch(RouterException $e)
		{
			return "INVALID_LINK";
		}
	}

	if ($fullUrl)
	{
		$result = $router->createFullUrl($result);
	}

	return $result;
}

?>