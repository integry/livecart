<?php

/**
 * Translates interface text to current locale language
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_helpSeeAlsoItem($params, Smarty $smarty)
{	
	$topic = $smarty->get_template_vars('rootTopic')->getTopic($params['id']);
	
	$router = Router::getInstance();
	
	return '<li><a href="' . $router->createUrl(array('controller' => 'backend.help', 'action' => 'view', 'id' => $params['id'])) . '">' . $topic->getName() . '</a></li>';
}

?>