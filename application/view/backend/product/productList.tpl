{foreach from=$productList key=key item=product}
	<tr class="{if ($offset + $key) is even}even{else}odd{/if}">
		<td class="cell_cb"><input type="checkbox" class="checkbox" name="product[$categoryID][$product.ID]" /></td>
		<td class="cell_sku">{$product.sku}</td>
		<td class="cell_name"><a href="{link controller=backend.product action=edit id=$product.ID}">{$product.name_lang}</a></td>
		<td class="cell_manuf">{$product.Manufacturer.name}</td>
		<td class="cell_price">{$product.prices.calculated.EUR}</td>
		<td class="cell_stock{if ($product.stockCount lt 5)} lowStock{/if}">{$product.stockCount}</td>
		<td class="cell_enabled{if ($product.isEnabled == 0)} notEnabled{/if}">{$product.isEnabled}</td>
	</tr>
{/foreach}