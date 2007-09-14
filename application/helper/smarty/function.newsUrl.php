<?php

ClassLoader::import('application.helper.CreateHandleString');

/**
 * Generates news page URL
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 */
function smarty_function_newsUrl($params, LiveCartSmarty $smarty)
{		
	$news = $params['news'];	
			
	$urlParams = array('controller' => 'news', 
					   'action' => 'view', 
					   'handle' => createHandleString($news['title_lang']), 
					   'id' => $news['ID']);
					   
	return $smarty->getApplication()->getRouter()->createUrl($urlParams);	
}

?>