<div class="image">
	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">
	{if $product.DefaultImage.paths.2}
		{img src=$product.DefaultImage.paths.2 alt=$product.name_lang|escape}
	{else}
		{img src=image/missing_small.jpg alt=$product.name_lang|escape}
	{/if}
	</a>
</div>

<div class="title">
	<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
</div>

<div style="bottom: 0;">
	<div class="pricingInfo"><div><div>
		{if $product.isAvailable}
			<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}" class="addToCart">{t _add_to_cart}</a>
		{/if}
		<span class="price">{$product.formattedPrice.$currency}</span>
		<br class="clear" />
	</div></div></div>

	<div class="order">
		<div class="orderingControls">
			{if 'ENABLE_WISHLISTS'|config}
				<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" class="addToWishList">{t _add_to_wishlist}</a>
			{/if}
		</div>
	</div>
</div>