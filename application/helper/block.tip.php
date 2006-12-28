<?php

/**
 * Display a tip block
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_tip($params, $content, $smarty, &$repeat) 
{
	if (!$repeat)
	{
		$smarty->assign('tipContent', $content);
		return $smarty->display('block/tip.tpl');
	}
}

?>