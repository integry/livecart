<dl class="dl-horizontal" id="productMainDetails">

	{block PRODUCT-OVERVIEW-BEFORE}

	{if $product.Manufacturer.name}
		<dt class="manufacturer">{t _manufacturer}</dt>
		<dd><a href="{categoryUrl data=$product.Category addFilter=$manufacturerFilter}">{$product.Manufacturer.name}</a></dd>
	{/if}

	{if 'SHOW_PRODUCT_WEIGHT'|config && $product.shippingWeight}
		<dt class="weight">{t _weight}</td>
		<dd>
			{if 'METRIC' == 'UNIT_SYSTEM'|config}
				{$product.shippingWeight} {t _kg}
			{else}
				{$product.shippingWeight_english}
			{/if}
		</dd>
	{/if}

	{if $product.sku}
		<dt class="sku">{t _sku}</dt>
		<dd>{$product.sku}</dd>
	{/if}

	{if $product.stockCount && 'PRODUCT_DISPLAY_STOCK'|config}
		<dt class="stockCount">{t _in_stock}</dt>
		<dd>{$product.stockCount}</dd>
	{/if}

	{if $product.URL}
		<dt class="websiteUrl"><a href="{$product.URL}" target="_blank">{t _product_website}</a></dt>
	{/if}

	{block PRODUCT-OVERVIEW-AFTER}

</dl>
