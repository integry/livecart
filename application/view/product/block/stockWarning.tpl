{% if !product.isDownloadable || config('INVENTORY_TRACKING_DOWNLOADABLE') %}
	{% if !product.stockCount && config('PRODUCT_DISPLAY_NO_STOCK') %}
		<span class="label label-danger noStock">{t _no_stock}</span>
	{else if (product.stockCount <= config('LOW_STOCK')) && config('PRODUCT_DISPLAY_LOW_STOCK')}
		<span class="label label-warning lowStock">{t _low_stock}</span>
	{% endif %}
{% endif %}
