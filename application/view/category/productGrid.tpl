<table class="productGrid">
{foreach from=$products item=product name="productList"}
	{if 0 == $smarty.foreach.productList.index % 'LAYOUT_GRID_COLUMNS'|@config}
		<tr class="{if ($smarty.foreach.productList.index + 'LAYOUT_GRID_COLUMNS'|@config) >= $smarty.foreach.productList.total}last{/if}{if $smarty.foreach.productList.first} first{/if}">
	{/if}

	<td class="{if $product.isFeatured}featured{/if}{if 1 == ($smarty.foreach.productList.index + 1) % 'LAYOUT_GRID_COLUMNS'|@config} first{/if}{if 0 == ($smarty.foreach.productList.index + 1) % 'LAYOUT_GRID_COLUMNS'|@config} last{/if}">
		<div class="gridItemWrap1"><div class="gridItemWrap2"><div class="gridItemWrap3"><div class="gridItemWrap4">
			{include file="category/productGridItem.tpl"}
		</div></div></div></div>
	</td>

	{if 0 == (($smarty.foreach.productList.index + 1) % 'LAYOUT_GRID_COLUMNS'|@config)}
		</tr>
	{elseif $smarty.foreach.productList.last}
		<td class="last empty"></td>
		</tr>
	{/if}
{/foreach}
</table>

