{include file="product/block/smallImage.tpl"}

<div class="title">
	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
</div>

<div class="pricingInfo"><div><div><div>
	{include file="product/block/cartButton.tpl"}
	{include file="product/block/productPrice.tpl"}
	<div class="clear"></div>
</div></div></div></div>

<div class="order">
	<div class="orderingControls">
		{if $product.rating && 'ENABLE_RATINGS'|config}
			{include file="category/productListRating.tpl"}
			{if 'ENABLE_WISHLISTS'|config}
				<span class="listItemSeparator">|</span>
			{/if}
		{/if}

		{if 'ENABLE_WISHLISTS'|config}
			<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" rel="nofollow" class="addToWishList">{t _add_to_wishlist}</a>
		{/if}
	</div>
</div>

{if 'ENABLE_PRODUCT_COMPARE'|config}
<div class="compare">
	{include file="compare/block/compareLink.tpl"}
</div>
{/if}