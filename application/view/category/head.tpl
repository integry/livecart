{% if $manufacturerFilter && ($appliedFilters|@count == 1) && ($currentPage == 1) %}
	<h1>[[manufacturerFilter.name_lang]]</h1>

	{% if $products.0.Manufacturer.attributes || $products.0.Manufacturer.DefaultImage.ID %}
		{% if $products.0.Manufacturer.attributes %}
			<div id="productSpecification" class="manufacturerAttributes">
				<table class="productTable table table-striped">
					[[ partial('product/specificationTableBody.tpl', ['attributes': $products.0.Manufacturer.attributes, 'field': EavField, 'group': EavFieldGroup]) ]]
				</table>
			</div>
		{% endif %}

		{% if $products.0.Manufacturer.DefaultImage.ID %}
			<img src="[[products.0.Manufacturer.DefaultImage.urls.3]]" alt="[[products.0.Manufacturer.name_lang]]" class="manufacturerImage" />
		{% endif %}
	{% endif %}
{% else %}
	<h1>[[category.name_lang]]{% if $searchQuery %} &gt;&gt; "<span class="keywords">[[searchQuery]]</span>"{% endif %}</h1>
{% endif %}

{% if 'DISPLAY_CATEGORY_DESC'|config && $category.description_lang %}
	<div class="descr categoryDescr">
		<p>[[category.description_lang]]</p>
	</div>
{% endif %}

{block RELATED_CATEGORIES}