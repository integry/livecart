<?php

/**
 * Generates order form URL in backend
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 */
function smarty_function_backendOrderUrl($params, LiveCartSmarty $smarty)
{		
	$urlParams = array('controller' => 'backend.customerOrder', 
					   'action' => 'index' );
					   
	return $smarty->getApplication()->getRouter()->createUrl($urlParams) . '#order_' . (isset($params['order']) ? $params['order']['ID'] : '');
}

?>