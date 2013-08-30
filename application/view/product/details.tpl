{% if $product.longDescription_lang || $product.shortDescription_lang %}
<div id="descriptionSection" class="productSection description">
	<h2>{t _description}</h2>
	<div id="productDescription">
		{% if $product.longDescription_lang %}
			[[product.longDescription_lang]]
		{% else %}
			[[product.shortDescription_lang]]
		{% endif %}
	</div>
</div>
{% endif %}

{% if $product.attributes %}
<div id="specificationSection" class="productSection specification">
<h2>{t _spec}<small>{t _tab_specification}</small></h2>
<div id="productSpecification">
	<table class="productDetailsTable table table-striped">
		[[ partial('product/specificationTableBody.tpl', ['attributes': $product.attributes, 'field': SpecField, 'group': SpecFieldGroup]) ]]
	</table>
</div>
</div>
{% endif %}

{% if $related %}
<div id="relatedSection" class="productSection related">
<h2>{t _recommended}<small>{t _tab_recommended}</small></h2>
<div id="relatedProducts">
	{foreach from=$related item=group}
	   {% if $group.0.ProductRelationshipGroup.name_lang %}
		   <h3>[[group.0.ProductRelationshipGroup.name_lang]]</h3>
	   {% endif %}
	   [[ partial('category/productListLayout.tpl', ['layout': 'PRODUCT_PAGE_LIST_LAYOUT'|config, 'products': $group]) ]]
	{/foreach}
</div>
</div>
{% endif %}

{% if $additionalCategories %}
	[[ partial("product/block/additionalCategories.tpl") ]]
{% endif %}

{% if $together %}
<div id="purchasedTogetherSection" class="productSection purchasedTogether">
<h2>{t _purchased_together}<small>{t _tab_purchased}</small></h2>
<div id="purchasedTogether">
	[[ partial('category/productListLayout.tpl', ['layout': 'PRODUCT_PAGE_LIST_LAYOUT'|config, 'products': $together]) ]]
</div>
</div>
{% endif %}