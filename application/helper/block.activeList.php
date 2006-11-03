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
function smarty_block_activeList($params, $content, Smarty $smarty, &$repeat) 
{	
	$listId = $params['id'];

	// get list handler class and instance variable name
	$handlerClass = isset($params['handlerClass']) ? $params['handlerClass'] : $listId . 'Handler';
	$handlerVar = $listId . 'HandlerInstance';
		
	if ($repeat) 
	{
		// add handler variable name to param list (for activeListItem elements)
		$smarty->_tag_stack[count($smarty->_tag_stack) - 1][1]['handler'] = $handlerVar;
	}		
	else
	{
		// initialization script
		$script = new HtmlElement('script');
		$script->setContent('var ' . $handlerVar . ' = new ' . $handlerClass . '("' . $listId . '");');
		
		// list element
		$ul = new HtmlElement('ul');
		$ul->setAttribute('id', $listId);
		$ul->setAttribute('tabIndex', 0);		
		$ul->setAttribute('style', 'padding: 0px; display: table; width: 300px;');		
		$ul->setContent($content);

		// render list or single list item?
		if (true == $params['singleItem'])
		{
			return $content;
		}
		else
		{
  			return $ul->render() .
				   $script->render();
		}
	}	
}

?>