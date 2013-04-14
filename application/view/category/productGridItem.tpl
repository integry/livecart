<div class="thumbnail">

{include file="product/block/smallImage.tpl"}

<div class="caption">
	<h4>
		<a href="{productUrl product=$product filterChainHandle=$filterChainHandle category=$category}">{$product.name_lang}</a>
	</h4>

	<p class="pricingInfo">
		{include file="product/block/productPrice.tpl"}
		{include file="product/block/cartButton.tpl"}
	</p>

	<p class="productAction">
		{if $product.rating && 'ENABLE_RATINGS'|config}
			<span class="actionItem ratingItem">{include file="category/productListRating.tpl"}</span>
		{/if}

		{if 'ENABLE_WISHLISTS'|config}
			<span class="actionItem wishListItem"><span class="glyphicon glyphicon-heart-empty"></span> <a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" rel="nofollow" class="addToWishList">{t _add_to_wishlist}</a></span>
		{/if}

		{if 'ENABLE_PRODUCT_COMPARE'|config}
			<span class="actionItem compareItem"><span class="glyphicon glyphicon-eye-close"></span> {include file="compare/block/compareLink.tpl"}</span>
		{/if}
	</p>
</div>

</div>