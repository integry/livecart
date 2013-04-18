<div class="row productList">
	{foreach from=$products item=product name="productList"}
		<div class="col-span-12 {if $product.isFeatured}featured{/if}">
			{include file="category/productListItem.tpl"}
		</div>
	{/foreach}
</div>
