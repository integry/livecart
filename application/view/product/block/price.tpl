{if 'DISPLAY_PRICES'|config}
	<tr id="productPrice">
		<td class="param">{t _price}:</td>
		<td class="value price">
				{include file="product/block/productPagePrice.tpl"}
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