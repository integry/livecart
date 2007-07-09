<?php

/**
 * Smarty block plugin, for specifying JavaScript action for page menu item
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
 *			{menuAction}http://click.me.com{/menuAction} 
 *		{/menuItem}
 *		{menuItem}
 *			{menuCaption}Another menu item{/menuCaption}
 *			<strong>{pageAction}alert('Somebody clicked on me too!'){/menuAction}</strong> 
 *		{/menuItem}
 *  {/pageMenu}
 * </code>
 *
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_pageAction($params, $content, LiveCartSmarty $smarty, &$repeat) 
{	
	if (!$repeat) 
	{		
		$smarty->assign('menuPageAction', $content);
	}
}

?>