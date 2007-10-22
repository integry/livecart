<?php

/**
 * Generates order form URL in backend
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems 
 */
function smarty_function_backendOrderUrl($params, LiveCartSmarty $smarty)
{		
	$urlParams = array('controller' => 'backend.customerOrder', 
					   'action' => 'index' );
					   
	$url = $smarty->getApplication()->getRouter()->createUrl($urlParams, true) . '#order_' . (isset($params['order']) ? $params['order']['ID'] . '#tabOrderInfo__' : '');
	
	if (isset($params['url']))
	{
		$url = $smarty->getApplication()->getRouter()->createFullUrl($url);
	}
	
	return $url;
}

?>