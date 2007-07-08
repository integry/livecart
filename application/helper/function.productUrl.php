<?php

ClassLoader::import('application.helper.CreateHandleString');

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
	$handle = createHandleString($product['name_lang']);
		
	$urlParams = array('controller' => 'product', 
					   'action' => 'index', 
					   'producthandle' => $handle, 
					   'id' => $product['ID']);
					   
	$url = Router::getInstance()->createUrl($urlParams);	

    if (!empty($params['filterChainHandle']))
    {
        $url = Router::setUrlQueryParam($url, 'filters', $params['filterChainHandle']);
    }
    
    return $url;
}

?>