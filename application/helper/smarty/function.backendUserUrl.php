<?php

/**
 * Generates user form URL in backend
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 */
function smarty_function_backendUserUrl($params, LiveCartSmarty $smarty)
{		
	$urlParams = array('controller' => 'backend.userGroup', 
					   'action' => 'index' );
					   
	return $smarty->getApplication()->getRouter()->createUrl($urlParams) . '#user_' . (isset($params['user']) ? $params['user']['ID'] : '');
}

?>