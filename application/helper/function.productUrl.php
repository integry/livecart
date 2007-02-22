<?php

/**
 * Generates product page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_productUrl($params, Smarty $smarty)
{	
	$product = $params['product'];	
	$router = Router::getInstance();
	
	$handle = $product['handle'];
	
	if (!$handle)
	{
		$handle = 'temporaryhandle';
	}
	
	$urlParams = array('controller' => 'product', 
					   'action' => 'index', 
					   'producthandle' => $handle, 
					   'id' => $product['ID']);
					   
	return $router->createUrl($urlParams);
}

?>