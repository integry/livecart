<?php

ClassLoader::importNow('application.helper.CreateHandleString');

/**
 * Generates product page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_productUrl($params, LiveCartSmarty $smarty)
{
	return createProductUrl($params, $smarty->getApplication());
}

function createProductUrl($params, LiveCart $application)
{
	$product = $params['product'];
	$router = $application->getRouter();

	// use parent product data for child variations
	if (isset($product['Parent']))
	{
		$product = $product['Parent'];
	}

	$handle = createHandleString($product['name_lang']);

	$urlParams = array('controller' => 'product',
					   'action' => 'index',
					   'producthandle' => $handle,
					   'id' => $product['ID']);

	if (isset($params['query']))
	{
		$urlParams['query'] = $params['query'];
	}

	if (isset($params['context']))
	{
		$urlParams['query'] = (!empty($urlParams['query']) ? '&' : '') . $router->createUrlParamString($params['context']);
	}

	$url = $router->createUrl($urlParams, true);

	if (!empty($params['full']))
	{
		$url = $router->createFullUrl($url);
	}

	if (!empty($params['filterChainHandle']))
	{
		$url = $router->setUrlQueryParam($url, 'filters', $params['filterChainHandle']);
	}

	if (!empty($params['category']) && ($params['category']['ID'] != $product['categoryID']))
	{
		$url = $router->setUrlQueryParam($url, 'category', $params['category']['ID']);
	}

	$router->setLangReplace($handle, 'name', $product);

	return $url;
}

?>