<?php

/**
 * Form field error message block
 *
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_block_pageTitle($params, $content, $smarty, &$repeat) 
{
	$smarty->assign('PAGE_TITLE', $content);
}

?>