<div class="media {if $product.isFeatured}featured{/if}">
<div class="row">
	<div class="col col-lg-2">
		{include file="product/block/smallImage.tpl"}
	</div>

	<div class="col col-lg-8">
		{include file="category/block/productTitle.tpl"}

		{if $product.attributes}
			{include file="category/block/productListAttributes.tpl"}
		{/if}

		<p class="shortDescr">
			{block PRODUCT-LIST-DESCR-BEFORE}
			[[product.shortDescription_lang]]
			{block PRODUCT-LIST-DESCR-AFTER}
		</p>

		{include file="category/block/itemActions.tpl"}
	</div>

	<div class="col col-lg-2">
		<div class="pricingInfo">
			{include file="product/block/cartButton.tpl"}
			{include file="product/block/productPrice.tpl"}
		</div>
	</div>
</div>
</div>