{% extends "layout/frontend.tpl" %}

{% block title %}{t _select_shipping_addresses}{{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div class="checkoutHeader">
		{if $cart.cartItems}
			{include file="checkout/checkoutProgress.tpl" progress="progressCart" order=cart}
		{/if}
	</div>

	{if !$cart.cartItems && !$cart.wishListItems}
		<div style="clear: left;">
			{t _empty_basket}. <a href="{link route=$return}">{t _continue_shopping}</a>.
		</div>
	{else}

	{if $cart.cartItems}
		{include file="order/cartItems.tpl" multi=true}
	{/if}

	{if $cart.wishListItems && 'ENABLE_WISHLISTS'|config}
		[[ partial("order/wishList.tpl") ]]
	{/if}

	{/if}

	<div class="clear"></div>

{% endblock %}


<script type="text/javascript">
	new Order.OptionLoader($('cart'));
</script>