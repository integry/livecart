{% extends "layout/frontend.tpl" %}

{% title %}{t _your_basket}{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div class="checkoutHeader">
		{% if $cart.cartItems && !$isOnePageCheckout %}
			[[ partial('checkout/checkoutProgress.tpl', ['progress': "progressCart", 'order': cart]) ]]
		{% endif %}
	</div>

	[[ partial("order/changeMessages.tpl") ]]

	{% if !$cart.cartItems && !$cart.wishListItems %}
		<div style="clear: left;">
			{t _empty_basket}. <a href="[[ url(return) ]]">{t _continue_shopping}</a>.
		</div>
	{% else %}

	{% if $cart.cartItems %}
		[[ partial("order/cartItems.tpl") ]]
	{% endif %}

	{% if $cart.wishListItems && 'ENABLE_WISHLISTS'|config %}
		<div style="clear: left;">
			[[ partial("order/wishList.tpl") ]]
		</div>
	{% endif %}

	{% endif %}

	<div class="clear"></div>

{% endblock %}


<script type="text/javascript">
	new Order.OptionLoader($('cart'));
</script>