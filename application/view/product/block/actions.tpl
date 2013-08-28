{if $quantityPricing}
	<div class="row" id="quantityPrices">
		<div class="col col-lg-12">
			[[ partial("product/block/quantityPrice.tpl") ]]
		</div>
	</div>
{/if}

<div id="actionButtons">
{if 'ENABLE_WISHLISTS'|config}
	<p>
		<a class="btn btn-default btn-small" href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" rel="nofollow" class="addToWishList"><span class="glyphicon glyphicon-heart-empty"></span> {t _add_to_wishlist}</a>
	</p>
{/if}

{if 'ENABLE_PRODUCT_COMPARE'|config}
	<p>
		<a class="btn btn-default btn-small" href="{link compare/add id=$product.ID returnPath=true}" onclick="Compare.add(event)" class="addToCompare"><span class="glyphicon glyphicon-eye-close"></span> {t _add_compare}</a>
	</p>
{/if}
</div>
