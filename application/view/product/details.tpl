{if $product.longDescription_lang || $product.shortDescription_lang}
<h2>{t _description}</h2>
<div id="productDescription">
	{if $product.longDescription_lang}
		{$product.longDescription_lang}
	{else}
		{$product.shortDescription_lang}
	{/if}
</div>
{/if}

{if $product.attributes}
<h2>{t _spec}</h2>
<div id="productSpecification">
	<table class="productDetailsTable">
		{include file="product/specificationTableBody.tpl" attributes=$product.attributes field=SpecField group=SpecFieldGroup}
	</table>
</div>
{/if}

{if $related}
<h2>{t _recommended}</h2>
<div id="relatedProducts">

	{foreach from=$related item=group}

	   {if $group.0.ProductRelationshipGroup.name_lang}
		   <h3>{$group.0.ProductRelationshipGroup.name_lang}</h3>
	   {/if}

	   {include file="category/productList.tpl" products=$group}

	{/foreach}

</div>
{/if}

{if $additionalCategories}
	{include file="product/block/additionalCategories.tpl"}
{/if}

{if $together}
<h2>{t _purchased_together}</h2>
<div id="purchasedTogether">
	{include file="category/productList.tpl" products=$together}
</div>
{/if}