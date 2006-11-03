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
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_block_activeListItem($params, $content, Smarty $smarty, &$repeat) 
{	
	if (!$repeat) 
	{
		// get list info
		$listInfo = $smarty->_tag_stack[count($smarty->_tag_stack) - 2];
		$listParams = $listInfo[1];
		$listId = $listParams['id'];
		$listHandler = $listParams['handler'];
		$deletable = isset($listParams['deletable']) ? $listParams['deletable'] : 0;
		$sortable = isset($listParams['sortable']) ? $listParams['sortable'] : 0;
		
		// list item info
		$itemId = $params['id'];
		if (isset($params['sortable']) && 0 == $params['sortable'])
		{
		  	$sortable = 0;
		}

		if (isset($params['deletable']) && 0 == $params['deletable'])
		{
		  	$deletable = 0;
		}

		// create list element
		$li = new HtmlElement('li');
		$li->setAttribute('id', $listId . '_' . $itemId);
		$li->setAttribute('tabIndex', 1);
		$li->setAttribute('style', 'list-style: none; padding-bottom: 7px;');
		$li->setAttribute('onMouseOver', $listHandler . '.showMenu(this);');
		$li->setAttribute('onMouseOut', $listHandler . '.hideMenu(this);');
		$li->setAttribute('onKeyDown', $listHandler . '.navigate(event, this);');
		$li->setAttribute('onClick', 'this.focus();');

		// create menu element
		$menuContainer = new HtmlElement('span');										
		$menuContainer->setAttribute('style', 'visibility: hidden; vertical-align: top; display: table-cell; width: 25px; text-align: center;');
		
		$menu = new HtmlElement('span');
		$menuContent = array();

		// move command
		if ($sortable)
		{
		 	$moveHandle = new HtmlElement('img');
		 	$moveHandle->setAttribute('src', 'image/backend/list/move.png');
		 	$moveHandle->setAttribute('style', 'border: 0px;');
		 	$moveHandle->setAttribute('alt', $moveHint);
			$menuContent[] = $moveHandle->render();
		}

		// delete command
		if ($deletable)
		{
		 	$delHandle = new HtmlElement('a');
		 	$delHandle->setAttribute('href', '#');
		 	$delHandle->setAttribute('onClick', $listHandler . ".deleteItem('" . $itemId ."'); return false;");
		 	$delHandle->setAttribute('title', $delHint);

		 	$delIcon = new HtmlElement('img');
		 	$delIcon->setAttribute('src', 'image/backend/list/trash.png');
		 	$delIcon->setAttribute('style', 'border: 0px;');
		 	$delIcon->setAttribute('alt', $delHint);
			
			$delHandle->setContent($delIcon->render());
			$menuContent[] = $delHandle->render();
		}

		$menu->setContent(implode('<br />', $menuContent));
		$menuContainer->setContent($menu->render());
		
		// item info ("work area") container
		$itemContainer = new HtmlElement('span');
		$itemContainer->setAttribute('style', 'display: table-cell; padding-bottom: 10px;');		
		$itemContainer->setContent($content);
		
		// glue menu and work area together
		$li->setContent($menuContainer->render() .
						$itemContainer->render() 
					   );
					
		if (true == $listParams['singleItem'])
		{
		 	return $li->getContent(); 	
		}	
		else
		{
		  	return $li->render();
		}			
	}	
}

?>