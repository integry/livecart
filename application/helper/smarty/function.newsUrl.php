<?php

ClassLoader::importNow('application.helper.CreateHandleString');

/**
 * Generates news page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_newsUrl($params, LiveCartSmarty $smarty)
{
	return createNewsPostUrl($params, $smarty->getApplication());
}

function createNewsPostUrl($params, LiveCart $application)
{
	$news = $params['news'];

	$urlParams = array('controller' => 'news',
					   'action' => 'view',
					   'handle' => createHandleString($news['title_lang']),
					   'id' => $news['ID']);

	return $application->getRouter()->createUrl($urlParams, true);
}

?>