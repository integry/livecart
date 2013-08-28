{assign var=col value=(12/'LAYOUT_GRID_COLUMNS'|@config)|@round}

<div class="row productGrid">
{foreach from=$products item=product}
	<div class="productGridItem col col-lg-[[col]]{if $product.isFeatured} featured{/if}">
		{include file="category/productGridItem.tpl"}
	</div>
{/foreach}
</div>
