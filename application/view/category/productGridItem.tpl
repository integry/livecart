<div class="image">
	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">
	{if $product.DefaultImage.ID}
		{img src=$product.DefaultImage.paths.2 alt=$product.name_lang|escape}
	{else}
		{img src=image/missing_small.jpg alt=$product.name_lang|escape}
	{/if}
	</a>
</div>

<div class="title">
	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
</div>


<div class="pricingInfo"><div><div><div>
	{if $product.isAvailable && 'ENABLE_CART'|config}
		<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}" rel="nofollow" class="addToCart">{t _add_to_cart}</a>
	{/if}
	{if 'DISPLAY_PRICES'|config}
	<span class="price">
		{$product.formattedPrice.$currency}
		{if $product.formattedListPrice.$currency}
				<span class="listPrice">
					{$product.formattedListPrice.$currency}
				</span>
		{/if}
	</span>
	{/if}
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