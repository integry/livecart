<p class="productAction">
	{% if product.rating && config('ENABLE_RATINGS') %}
		<span class="actionItem ratingItem">[[ partial("category/productListRating.tpl") ]]</span>
	{% endif %}

	{% if config('ENABLE_WISHLISTS') %}
		<span class="actionItem wishListItem"><span class="glyphicon glyphicon-heart-empty"></span> <a href="{link controller=order action=addToWishList id=product.ID returnPath=true}" rel="nofollow" class="addToWishList">{t _add_to_wishlist}</a></span>
	{% endif %}

	{% if config('ENABLE_PRODUCT_COMPARE') %}
		<span class="actionItem compareItem"><span class="glyphicon glyphicon-eye-close"></span> [[ partial("compare/block/compareLink.tpl") ]]</span>
	{% endif %}
</p>
