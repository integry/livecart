<?php

/**
 * Smarty block plugin, for specifying menu item caption (title)
 * This block must always be called in menuItem block context
 *
 * @param array $params
 * @param Smarty $smarty
 * @param $repeat
 *
 * <code>
 *	{pageMenu id="menu"}
 *		{menuItem}
 *			<strong>{menuCaption}Click Me{/menuCaption}</strong>
 *			{menuAction}http://click.me.com{/menuAction} 
 *		{/menuItem}
 *		{menuItem}
 *			<strong>{menuCaption}Another menu item{/menuCaption}</strong>
 *			{pageAction}alert('Somebody clicked on me too!'){/menuAction}
 *		{/menuItem}
 *  {/pageMenu}
 * </code>
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_menuCaption($params, $content, LiveCartSmarty $smarty, &$repeat) 
{	
	if (!$repeat) 
	{		
		$smarty->assign('menuCaption', $content);
	}
}

?>