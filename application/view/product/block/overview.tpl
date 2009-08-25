<table id="productMainDetails">
	{if $product.Manufacturer.name}
	<tr>
		<td class="param">{t _manufacturer}:</td>
		<td class="value"><a href="{categoryUrl data=$product.Category addFilter=$manufacturerFilter}">{$product.Manufacturer.name}</a></td>
	</tr>
	{/if}

	{if 'SHOW_PRODUCT_WEIGHT'|config && $product.shippingWeight}
	<tr>
		<td class="param">{t _weight}:</td>
		<td class="value">
			{if 'METRIC' == 'UNIT_SYSTEM'|config}
				{$product.shippingWeight} {t _kg}
			{else}
				{$product.shippingWeight_english}
			{/if}
		</td>
	</tr>
	{/if}

	{if $product.sku}
	<tr>
		<td class="param">{t _sku}:</td>
		<td class="value">{$product.sku}</td>
	</tr>
	{/if}

	{if $product.stockCount && 'PRODUCT_DISPLAY_STOCK'|config}
	<tr>
		<td class="param">{t _in_stock}:</td>
		<td class="value">{$product.stockCount}</td>
	</tr>
	{/if}

	{if !$product.isDownloadable || 'INVENTORY_TRACKING_DOWNLOADABLE'|config}
		{if !$product.stockCount && 'PRODUCT_DISPLAY_NO_STOCK'|config}
		<tr>
			<td colspan="2" class="noStock"><span>{t _no_stock}</span></td>
		</tr>
		{/if}

		{if $product.stockCount && 'PRODUCT_DISPLAY_LOW_STOCK'|config}
		<tr>
			<td colspan="2" class="lowStock"><span>{t _low_stock}</span></td>
		</tr>
		{/if}
	{/if}

	{if $product.URL}
	<tr>
		<td colspan="2" class="websiteUrl"><a href="{$product.URL}" target="_blank">{t _product_website}</a></td>
	</tr>
	{/if}

</table>