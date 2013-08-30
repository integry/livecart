<fieldset>
	<legend>{t _main_details}</legend>

	[[ selectfld('isEnabled', tip( '_availability'), productStatuses) ]]

	{input name="name"}
		{label}{t _product_name}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=name"}
	{/input}

	[[ checkbox('autosku', '_generate_sku') ]]

	{input name="sku"}
		{label}{tip _sku_code _hint_sku}:{/label}
		{textfield class="product_sku" ng_disabled="product.autosku == true" autocomplete="controller=backend.product field=sku"}
	{/input}

	{input name="shortDescription"}
		{label}{tip _short_description _hint_shortdescr}:{/label}
		{textarea tinymce=true class="shortDescr tinyMCE"}
	{/input}

	{input name="longDescription"}
		{label}{tip _long_description _hint_longdescr}:{/label}
		{textarea tinymce=true class="longDescr tinyMCE"}
	{/input}

	[[ selectfld('type', tip( '_product_type'), productTypes) ]]

	{input name="URL"}
		{label}{tip _website_address}:{/label}
		{textfield class="wide" autocomplete="controller=backend.product field=URL"}
	{/input}

	{input name="Manufacturer.name"}
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

	{% if $shippingClasses %}
		{input name="shippingClassID"}
			{label}{tip _shippingClass}:{/label}
			{selectfield options=$shippingClasses class="shippingClassID"}
		{/input}
	{% endif %}

	{% if $taxClasses %}
		{input name="taxClassID"}
			{label}{tip _taxClass}:{/label}
			{selectfield options=$taxClasses class="taxClassID"}
		{/input}
	{% endif %}

	{input name="position"}
		{label}{tip _sort_order _hint_sort_order}:{/label}
		{textfield class="number" number=true}
	{/input}

	[[ checkbox('isFeatured', tip('_mark_as_featured_product _hint_featured')) ]]

</fieldset>