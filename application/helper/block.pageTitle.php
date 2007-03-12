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
		$content .= '<script type="text/javascript">Backend.setHelpContext("' . $params['help'] . '")</script>';
	}
	$GLOBALS['PAGE_TITLE'] = $content;
	$smarty->assign('PAGE_TITLE', $content);
}

?>