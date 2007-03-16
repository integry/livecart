{foreach from=$productList key=key item=product}
	<tr class="{if ($offset + $key) is even}even{else}odd{/if}">
		<td class="cell_cb"><input type="checkbox" class="checkbox" name="product[{$product.ID}]" /></td>
		<td class="cell_sku">{$product.sku}</td>
		<td class="cell_name">
		    <span><span class="progressIndicator" id="productIndicator_{$product.ID}" style="display: none;"></span></span>
            
            <a href="#edit" onclick="if(window.selectProductPopup) window.selectProductPopup.getSelectedProduct({$product.ID}); else window.openProduct({$product.ID}, event); return false;">{$product.name_lang}</a>
        </td>
		<td class="cell_manuf">{$product.Manufacturer.name}</td>
		<td class="cell_price">{$product.price_EUR}</td>
		<td class="cell_stock{if (($product.stockCount) < 5)} lowStock{/if}">{$product.stockCount}</td>
		<td class="cell_enabled{if (!$product.isEnabled)} notEnabled{/if}">{if $product.isEnabled}{t _yes}{else}{t _no}{/if}</td>
	</tr>
{/foreach}

