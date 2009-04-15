{if 'DISPLAY_PRICES'|config}
	<tr id="productPrice">
		<td class="param">{t _price}:</td>
		<td class="value price">
				<span class="realPrice">{$product.formattedPrice.$currency}</span>
			{if $product.formattedListPrice.$currency}
				<span class="listPrice">
					{$product.formattedListPrice.$currency}
				</span>
			{/if}
		</td>
	</tr>
	{if $quantityPricing}
		<tr>
			<td colspan="2">
				{include file="product/block/quantityPrice.tpl"}
			</td>
		</tr>
	{/if}
{/if}