{if !$layout}
	{assign var=layout value='LIST_LAYOUT'|config}
{/if}

{if 'GRID' == $layout}
	{include file="category/productGrid.tpl" products=$products}
{elseif $layout == 'TABLE'}
	{include file="category/productTable.tpl" products=$products}
{else}
	{include file="category/productList.tpl" products=$products}
{/if}
