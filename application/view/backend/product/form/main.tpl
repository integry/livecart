<div>
	<h2>{t _main_details}</h2>

	[[ selectfld('isEnabled', tip( '_availability'), productStatuses) ]]

	[[ textfld('name', tip('_product_name')) ]]
	
	[[ checkbox('autosku', '_sku_code') ]]

	[[ textfld('sku', tip('_hint_sku'), ['ng-disabled': 'vals.autosku']) ]]

	[[ textareafld('shortDescription', tip('_short_description'), ['ui-my-tinymce': '']) ]]
	[[ textareafld('longDescription', tip('_long_description'), ['ui-my-tinymce': '']) ]]
	
	{# [[ selectfld('type', tip( '_product_type'), productTypes) ]] #}

	[[ textfld('URL', tip('_website_address')) ]]

	{#
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
	#}

	{#
	{% if !empty(shippingClasses) %}
		{input name="shippingClassID"}
			{label}{tip _shippingClass}:{/label}
			{selectfield options=shippingClasses class="shippingClassID"}
		{/input}
	{% endif %}

	{% if !empty(taxClasses) %}
		{input name="taxClassID"}
			{label}{tip _taxClass}:{/label}
			{selectfield options=taxClasses class="taxClassID"}
		{/input}
	{% endif %}

	{input name="position"}
		{label}{tip _sort_order _hint_sort_order}:{/label}
		{textfield class="number" number=true}
	{/input}
	#}

	[[ checkbox('isFeatured', tip('_mark_as_featured_product')) ]]
	
	<eav-fields config="eav"></eav-fields>

</div>
