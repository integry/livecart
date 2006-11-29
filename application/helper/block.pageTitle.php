<?php

/**
 * Set page title
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_pageTitle($params, $content, $smarty, &$repeat) 
{
	if (isset($params['help']))
	{
		$router = Router::getInstance();
		$url = $router->createUrl(array('controller' => 'backend.help', 'action' => 'view', 'id' => $params['help']));
		$help = '<a href="#" onClick="var helpWindow = showHelp(\''.$url.'\'); helpWindow.focus(); return false;"><img src="image/silk/help.png"/></a>';
		$content .= $help;
	}
	
	$smarty->assign('PAGE_TITLE', $content);
}

?>