<?php

/**
 * Inserts a base URL string
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_helpLink($params, $smarty)
{
	$topic = $params['id'];
	$current = $smarty->_tpl_vars['topic']['ID'];
	
	if (substr($topic, 0, 1) == '.')
	{
		$topic = $current . $topic;		
	}
	elseif (substr($topic, 0, 1) == '/')
	{
		$topic = substr($topic, 1);
	}
	else	
	{
		$root = substr($topic, strrpos('.', $topic));
		$topic = $root . '.' . $topic;			
	}
	
	return Router::getInstance()->createUrl(array('controller' => 'help', 'action' => 'view', 'id' => $topic));
}

?>