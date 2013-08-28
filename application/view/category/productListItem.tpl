<div class="media {if $product.isFeatured}featured{/if}">
<div class="row">
	<div class="col col-lg-2">
		[[ partial("product/block/smallImage.tpl") ]]
	</div>

	<div class="col col-lg-8">
		[[ partial("category/block/productTitle.tpl") ]]

		{if $product.attributes}
			[[ partial("category/block/productListAttributes.tpl") ]]
		{/if}

		<p class="shortDescr">
			{block PRODUCT-LIST-DESCR-BEFORE}
			[[product.shortDescription_lang]]
			{block PRODUCT-LIST-DESCR-AFTER}
		</p>

		[[ partial("category/block/itemActions.tpl") ]]
	</div>

	<div class="col col-lg-2">
		<div class="pricingInfo">
			[[ partial("product/block/cartButton.tpl") ]]
			[[ partial("product/block/productPrice.tpl") ]]
		</div>
	</div>
</div>
</div>