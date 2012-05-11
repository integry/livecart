<?php

/**
 * Generates user form URL in backend
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_backendUserUrl($params, Smarty_Internal_Template $smarty)
{
	if (!isset($params['user']) && isset($params['id']))
	{
		$params['user'] = array('ID' => $params['id']);
	}

	$urlParams = array('controller' => 'backend.userGroup',
					   'action' => 'index' );

	return $smarty->getApplication()->getRouter()->createUrl($urlParams, true) . '#user_' . (isset($params['user']) ? $params['user']['ID'] : '');
}

?>