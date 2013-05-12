<fieldset>
	<legend>{t _main_details}</legend>

	{input name="isEnabled"}
		{label}{tip _availability}:{/label}
		{selectfield options=$productStatuses}
	{/input}

	<div style="border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 4px;"></div>

	{input name="name"}
		{label}{t _product_name}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=name"}
	{/input}

	{input name="autosku"}
		{checkbox onclick="Backend.Product.toggleSkuField(this);"}
		{label}{t _generate_sku}{/label}
	{/input}

	{input name="sku"}
		{label}{tip _sku_code _hint_sku}:{/label}
		{textfield class="product_sku" autocomplete="controller=backend.product field=sku"}
	{/input}

	{input name="shortDescription"}
		{label}{tip _short_description _hint_shortdescr}:{/label}
		{textarea class="shortDescr tinyMCE"}
	{/input}

	{input name="longDescription"}
		{label}{tip _long_description _hint_longdescr}:{/label}
		{textarea class="longDescr tinyMCE"}
	{/input}

	{input name="type"}
		{label}{tip _product_type}:{/label}
		{selectfield options=$productTypes id="product_`$cat`_`$product.ID`_type"}
	{/input}

	{input name="URL"}
		{label}{tip _website_address}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=URL"}
	{/input}

	{input name="manufacturer"}
		{label}{t _manufacturer}:{/label}
		{textfield class="wide" autocomplete="controller=backend.manufacturer field=manufacturer"}
	{/input}

	{input name="keywords"}
		{label}{tip _keywords _hint_keywords}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=keywords"}
	{/input}

	{input name="pageTitle"}
		{label}{tip _pageTitle _hint_pageTitle}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=pageTitle"}
	{/input}

	{if $shippingClasses}
		{input name="shippingClassID"}
			{label}{tip _shippingClass}:{/label}
			{selectfield options=$shippingClasses class="shippingClassID"}
		{/input}
	{/if}

	{if $taxClasses}
		{input name="taxClassID"}
			{label}{tip _taxClass}:{/label}
			{selectfield options=$taxClasses class="taxClassID"}
		{/input}
	{/if}

	{input name="position"}
		{label}{tip _sort_order _hint_sort_order}:{/label}
		{textfield class="number"}
	{/input}

	{input name="isFeatured"}
		{checkbox}
		{label}{tip _mark_as_featured_product _hint_featured}{/label}
	{/input}

</fieldset>