{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_basket}{{% endblock %}
{include file="checkout/layout.tpl"}
{% block content %}

	<div class="checkoutHeader">
		{if $cart.cartItems && !$isOnePageCheckout}
			{include file="checkout/checkoutProgress.tpl" progress="progressCart" order=cart}
		{/if}
	</div>

	{include file="order/changeMessages.tpl"}

	{if !$cart.cartItems && !$cart.wishListItems}
		<div style="clear: left;">
			{t _empty_basket}. <a href="{link route=$return}">{t _continue_shopping}</a>.
		</div>
	{else}

	{if $cart.cartItems}
		{include file="order/cartItems.tpl"}
	{/if}

	{if $cart.wishListItems && 'ENABLE_WISHLISTS'|config}
		<div style="clear: left;">
			{include file="order/wishList.tpl"}
		</div>
	{/if}

	{/if}

	<div class="clear"></div>

{% endblock %}


<script type="text/javascript">
	new Order.OptionLoader($('cart'));
</script>