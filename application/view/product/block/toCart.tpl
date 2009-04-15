{if $product.isAvailable && 'ENABLE_CART'|config}

	{block PRODUCT-OPTIONS}
	{block PRODUCT-VARIATIONS}

	<tr id="productToCart" class="cartLinks">
		<td class="param">{t _quantity}:</td>
		<td class="value">
			{include file="product/block/quantity.tpl"}
			<input type="submit" class="submit" value="{tn _add_to_cart}" />
			{hidden name="return" value=$catRoute}
		</td>
	</tr>
{/if}

