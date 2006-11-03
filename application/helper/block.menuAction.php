<?php

/**
 * Smarty block plugin, for specifying URL action for page menu item
 * This block must always be called in menuItem block context
 *
 * @param array $params
 * @param Smarty $smarty
 * @param $repeat
 *
 * <code>
 *	{pageMenu id="menu"}
 *		{menuItem}
 *			{menuCaption}Click Me{/menuCaption}
 *			<strong>{menuAction}http://click.me.com{/menuAction}</strong> 
 *		{/menuItem}
 *		{menuItem}
 *			{menuCaption}Another menu item{/menuCaption}
 *			{pageAction}alert('Somebody clicked on me too!'){/menuAction}
 *		{/menuItem}
 *  {/pageMenu}
 * </code>
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_menuAction($params, $content, Smarty $smarty, &$repeat) 
{	
	if (!$repeat) 
	{		
		$smarty->assign('menuAction', $content);
	}
}

?>