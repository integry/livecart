{if $product.isAvailable && 'ENABLE_CART'|config}
	<a href="{link controller=order action=addToCart id=$product.ID returnPath=true}" rel="nofollow" class="addToCart"><span>{t _add_to_cart}</span></a>
{/if}
