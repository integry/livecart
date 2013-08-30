{% if !$product.isDownloadable || 'INVENTORY_TRACKING_DOWNLOADABLE'|config %}
	{% if !$product.stockCount && 'PRODUCT_DISPLAY_NO_STOCK'|config %}
		<span class="label label-danger noStock">{t _no_stock}</span>
	{else if ($product.stockCount <= 'LOW_STOCK'|config) && 'PRODUCT_DISPLAY_LOW_STOCK'|config}
		<span class="label label-warning lowStock">{t _low_stock}</span>
	{% endif %}
{% endif %}
