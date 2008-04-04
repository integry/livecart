{if $tree}
	{if $tree.ID}<item child="{$tree.childrenCount}" id="{$tree.ID}" text="{$tree.name_lang|escape:'html'}" {if !$doNotTouch && $tree.ID == $targetID}selected="true" call="true"{/if}{if !$tree.isEnabled} style="color: #999;"{/if}>{/if}
		{foreach from=$tree.children key="name" item="subtree"}
			  {include file="backend/category/xmlNodes.tpl" tree=$subtree}
		{/foreach}
	{if $tree.ID}</item>{/if}
{/if}
