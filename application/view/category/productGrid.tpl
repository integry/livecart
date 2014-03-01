{% set col=(12/config('LAYOUT_GRID_COLUMNS')) %}

<div class="row productGrid">
{% for product in products %}
	<div class="productGridItem col-sm-[[col]]{% if product.isFeatured %} featured{% endif %}">
		[[ partial("category/productGridItem.tpl") ]]
	</div>
{% endfor %}
</div>
