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
function smarty_function_helpSeeAlsoItem($params, LiveCartSmarty $smarty)
{	
	$topic = $smarty->get_template_vars('rootTopic')->getTopic($params['id']);
	
	$name = ($topic instanceof HelpTopic) ? $topic->getName() : '<span style="color:red;font-weight: bold; font-size: larger;">INVALID LINK</span>';
	
	return '<li><a href="' . $smarty->getApplication()->getRouter()->createUrl(array('controller' => 'help', 'action' => 'view', 'id' => $params['id'])) . '">' . $name . '</a></li>';
}

?>