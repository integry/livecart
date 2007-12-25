<table class="productGrid">
{foreach from=$products item=product name="productList"}
	{if 0 == $smarty.foreach.productList.index % 'LAYOUT_GRID_COLUMNS'|@config}
		<tr class="{if ($smarty.foreach.productList.index + 'LAYOUT_GRID_COLUMNS'|@config) >= $smarty.foreach.productList.total}last{/if}{if $smarty.foreach.productList.first} first{/if}">
	{/if}

	<td class="{if $product.isFeatured}featured{/if}{if 0 == ($smarty.foreach.productList.index + 1) % 'LAYOUT_GRID_COLUMNS'|@config} last{/if}">

		{include file="category/productGridItem.tpl"}

	</td>

	{if 0 == ($smarty.foreach.productList.index + 1) % 'LAYOUT_GRID_COLUMNS'|@config}
		</tr>
	{/if}
{/foreach}
</table>