<div class="row-fluid">
	<ul class="thumbnails productList">
	{foreach from=$products item=product name="productList"}
		<li class="col-span-12 {if $product.isFeatured}featured{/if}">
			{include file="category/productListItem.tpl"}

			{if !$smarty.foreach.productList.last}
				<div class="productSeparator"></div>
			{/if}
		</li>
	{/foreach}
	</ul>
</div>
