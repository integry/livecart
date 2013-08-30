<dl class="dl-horizontal" id="productMainDetails">

	{block PRODUCT-OVERVIEW-BEFORE}

	{% if $product.Manufacturer.name %}
		<dt class="manufacturer">{t _manufacturer}</dt>
		<dd><a href="{categoryUrl data=$product.Category addFilter=$manufacturerFilter}">[[product.Manufacturer.name]]</a></dd>
	{% endif %}

	{% if 'SHOW_PRODUCT_WEIGHT'|config && $product.shippingWeight %}
		<dt class="weight">{t _weight}</td>
		<dd>
			{% if 'METRIC' == 'UNIT_SYSTEM'|config %}
				[[product.shippingWeight]] {t _kg}
			{% else %}
				[[product.shippingWeight_english]]
			{% endif %}
		</dd>
	{% endif %}

	{% if $product.sku %}
		<dt class="sku">{t _sku}</dt>
		<dd>[[product.sku]]</dd>
	{% endif %}

	{% if $product.stockCount && 'PRODUCT_DISPLAY_STOCK'|config %}
		<dt class="stockCount">{t _in_stock}</dt>
		<dd>[[product.stockCount]]</dd>
	{% endif %}

	{% if $product.URL %}
		<dt class="websiteUrl"><a href="[[product.URL]]" target="_blank">{t _product_website}</a></dt>
	{% endif %}

	{block PRODUCT-OVERVIEW-AFTER}

</dl>
