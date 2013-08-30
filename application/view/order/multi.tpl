{% extends "layout/frontend.tpl" %}

{% block title %}{t _select_shipping_addresses}{{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div class="checkoutHeader">
		{% if $cart.cartItems %}
			[[ partial('checkout/checkoutProgress.tpl', ['progress': "progressCart", 'order': cart]) ]]
		{% endif %}
	</div>

	{% if !$cart.cartItems && !$cart.wishListItems %}
		<div style="clear: left;">
			{t _empty_basket}. <a href="[[ url(return) ]]">{t _continue_shopping}</a>.
		</div>
	{% else %}

	{% if $cart.cartItems %}
		[[ partial('order/cartItems.tpl', ['multi': true]) ]]
	{% endif %}

	{% if $cart.wishListItems && 'ENABLE_WISHLISTS'|config %}
		[[ partial("order/wishList.tpl") ]]
	{% endif %}

	{% endif %}

	<div class="clear"></div>

{% endblock %}


<script type="text/javascript">
	new Order.OptionLoader($('cart'));
</script>