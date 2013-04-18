{if $product.isAvailable && 'ENABLE_CART'|config}
	<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}" rel="nofollow" class="btn btn-success addToCart">
		<span class="glyphicon glyphicon-shopping-cart"></span>
		<span class="buttonCaption">{t _add_to_cart}</span>
	</a>
{/if}
