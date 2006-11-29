<?php

/**
 * Set page title
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_pageTitle($params, $content, $smarty, &$repeat) 
{
	$smarty->assign('PAGE_TITLE', $content);
}

/*
<a href=""><img src="image/silk/help.png" onClick="showHelp('{link controller=backend.help action=view id=language.index}'); return false;" /></a>
*/

?>