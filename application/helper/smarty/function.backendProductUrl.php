<?php

/**
 * Generates product form URL in backend
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_backendProductUrl($params, Smarty_Internal_Template $smarty)
{
	if (!isset($params['product']) && isset($params['id']))
	{
		$params['product'] = array('ID' => $params['id']);
	}

	$product = $params['product'];

	$urlParams = array('controller' => 'backend.category',
					   'action' => 'index' );

	return $smarty->getApplication()->getRouter()->createUrl($urlParams, true) . '#product_' . (!empty($product['Parent']['ID']) ? $product['Parent']['ID'] : $product['ID']);
}

?>