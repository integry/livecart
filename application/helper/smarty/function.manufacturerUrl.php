<?php

include_once dirname(__file__) . '/function.categoryUrl.php';

/**
 * Generates manufacturer page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_manufacturerUrl($params, LiveCartSmarty $smarty)
{
	$manufacturer = $params['data'];
	$params['data'] =& Category::getRootNode()->toArray();
	$params['addFilter'] = new ManufacturerFilter($manufacturer['ID'], $manufacturer['name']);
	return createCategoryUrl($params, $smarty->getApplication());
}


?>