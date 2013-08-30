<p class="productAction">
	{% if $product.rating && 'ENABLE_RATINGS'|config %}
		<span class="actionItem ratingItem">[[ partial("category/productListRating.tpl") ]]</span>
	{% endif %}

	{% if 'ENABLE_WISHLISTS'|config %}
		<span class="actionItem wishListItem"><span class="glyphicon glyphicon-heart-empty"></span> <a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" rel="nofollow" class="addToWishList">{t _add_to_wishlist}</a></span>
	{% endif %}

	{% if 'ENABLE_PRODUCT_COMPARE'|config %}
		<span class="actionItem compareItem"><span class="glyphicon glyphicon-eye-close"></span> [[ partial("compare/block/compareLink.tpl") ]]</span>
	{% endif %}
</p>
