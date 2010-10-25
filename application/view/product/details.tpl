{if $product.longDescription_lang || $product.shortDescription_lang}
<div id="descriptionSection" class="productSection description">
	<h2>{t _description}</h2>
	<div id="productDescription">
		{if $product.longDescription_lang}
			{$product.longDescription_lang}
		{else}
			{$product.shortDescription_lang}
		{/if}
	</div>
</div>
{/if}

{if $product.attributes}
<div id="specificationSection" class="productSection specification">
<h2>{t _spec}</h2>
<div id="productSpecification">
	<table class="productDetailsTable">
		{include file="product/specificationTableBody.tpl" attributes=$product.attributes field=SpecField group=SpecFieldGroup}
	</table>
</div>
</div>
{/if}

{if $related}
<div id="relatedSection" class="productSection related">
<h2>{t _recommended}</h2>
<div id="relatedProducts">
	{foreach from=$related item=group}
	   {if $group.0.ProductRelationshipGroup.name_lang}
		   <h3>{$group.0.ProductRelationshipGroup.name_lang}</h3>
	   {/if}
	   {include file="category/productListLayout.tpl" layout='PRODUCT_PAGE_LIST_LAYOUT'|config products=$group}
	{/foreach}
</div>
</div>
{/if}

{if $additionalCategories}
	{include file="product/block/additionalCategories.tpl"}
{/if}

{if $together}
<div id="purchasedTogetherSection" class="productSection purchasedTogether">
<h2>{t _purchased_together}</h2>
<div id="purchasedTogether">
	{include file="category/productListLayout.tpl" layout='PRODUCT_PAGE_LIST_LAYOUT'|config products=$together}
</div>
</div>
{/if}