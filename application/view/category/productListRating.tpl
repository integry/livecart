<span class="productListRating">
	<img src="image/rating/{ {$product.rating*2|@round}/2}.gif" />
	{if $product.reviewCount}
		<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}#reviews">([[product.reviewCount]])</a>
	{/if}
</span>
