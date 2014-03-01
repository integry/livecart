{% if product.longDescription() || product.shortDescription() %}
<div id="descriptionSection" class="productSection description">
	<h2>{t _description}</h2>
	<div id="productDescription">
		{% if product.longDescription() %}
			[[product.longDescription()]]
		{% else %}
			[[product.shortDescription()]]
		{% endif %}
	</div>
</div>
{% endif %}

{% if product.attributes %}
<div id="specificationSection" class="productSection specification">
<h2>{t _spec}<small>{t _tab_specification}</small></h2>
<div id="productSpecification">
	<table class="productDetailsTable table table-striped">
		[[ partial('product/specificationTableBody.tpl', ['attributes': product.attributes, 'field': SpecField, 'group': SpecFieldGroup]) ]]
	</table>
</div>
</div>
{% endif %}

{% if !empty(related) %}
<div id="relatedSection" class="productSection related">
<h2>{t _recommended}<small>{t _tab_recommended}</small></h2>
<div id="relatedProducts">
	{% for group in related %}
	   {% if group.0.ProductRelationshipGroup.name() %}
		   <h3>[[group.0.ProductRelationshipGroup.name()]]</h3>
	   {% endif %}
	   [[ partial('category/productListLayout.tpl', ['layout': config('PRODUCT_PAGE_LIST_LAYOUT'), 'products': group]) ]]
	{% endfor %}
</div>
</div>
{% endif %}

{% if !empty(additionalCategories) %}
	[[ partial("product/block/additionalCategories.tpl") ]]
{% endif %}

{% if !empty(together) %}
<div id="purchasedTogetherSection" class="productSection purchasedTogether">
<h2>{t _purchased_together}<small>{t _tab_purchased}</small></h2>
<div id="purchasedTogether">
	[[ partial('category/productListLayout.tpl', ['layout': config('PRODUCT_PAGE_LIST_LAYOUT'), 'products': together]) ]]
</div>
</div>
{% endif %}