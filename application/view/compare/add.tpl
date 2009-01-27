{if $products|@count == 1}
	{include file="block/compareMenu.tpl"}
{elseif $added}
	{include file="compare/block/item.tpl" product=$products[$added]}
{/if}