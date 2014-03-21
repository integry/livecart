{% if product.isAvailable() and config('ENABLE_CART') %}
	<a href="[[ url("order/addToCart", ['id': product.ID]) ]]" rel="nofollow" class="btn btn-success addToCart">
		<span class="glyphicon glyphicon-shopping-cart"></span>
		<span class="buttonCaption">{t _add_to_cart}</span>
	</a>
{% endif %}
