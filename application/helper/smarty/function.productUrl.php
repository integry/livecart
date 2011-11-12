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

	$url = $application->getRouter()->createUrl($urlParams, true);

	if (!empty($params['full']))
	{
		$url = $application->getRouter()->createFullUrl($url);
	}

	if (!empty($params['filterChainHandle']))
	{
		$url = $application->getRouter()->setUrlQueryParam($url, 'filters', $params['filterChainHandle']);
	}

	if (!empty($params['category']) && ($params['category']['ID'] != $product['categoryID']))
	{
		$url = $application->getRouter()->setUrlQueryParam($url, 'category', $params['category']['ID']);
	}

	$application->getRouter()->setLangReplace($handle, 'name', $product);

	return $url;
}

?>