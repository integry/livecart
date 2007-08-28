<?php

ClassLoader::import('application.helper.CreateHandleString');

/**
 * Generates product page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 */
function smarty_function_productUrl($params, LiveCartSmarty $smarty)
{		
	$product = $params['product'];	
	$handle = createHandleString($product['name_lang']);
		
	$urlParams = array('controller' => 'product', 
					   'action' => 'index', 
					   'producthandle' => $handle, 
					   'id' => $product['ID']);
					   
	$url = $smarty->getApplication()->getRouter()->createUrl($urlParams);	

    if (!empty($params['filterChainHandle']))
    {
        $url = $smarty->getApplication()->getRouter()->setUrlQueryParam($url, 'filters', $params['filterChainHandle']);
    }
    
    return $url;
}

?>