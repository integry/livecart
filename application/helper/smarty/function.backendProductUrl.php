<?php

/**
 * Generates product form URL in backend
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems 
 */
function smarty_function_backendProductUrl($params, LiveCartSmarty $smarty)
{		
	$product = $params['product'];	
		
	$urlParams = array('controller' => 'backend.category', 
					   'action' => 'index' );
					   
	return $smarty->getApplication()->getRouter()->createUrl($urlParams) . '#product_' . $product['ID'];
}

?>