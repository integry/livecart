<?php

/**
 * Smarty block plugin, for generating page menus
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
 *			{pageAction}alert('Somebody clicked on me too!'){/menuAction}
 *		{/menuItem}
 *  {/pageMenu}
 * </code>
 *
 * @return string Menu HTML code
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_block_pageMenu($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if ($repeat)
	{
		$smarty->clear_assign('pageMenuItems');
	}
	else
	{
		$items = $smarty->getTemplateVars('pageMenuItems');

		$menuDiv = new HtmlElement('div');
		$menuDiv->setAttribute('id', $params['id']);
		$menuDiv->setAttribute('tabIndex', 1);
		$menuDiv->setContent(implode(' | ', $items));

		return $menuDiv->render();
	}

}

?>