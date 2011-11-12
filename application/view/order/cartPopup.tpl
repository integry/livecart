<a class="cancel cartPopupClose popupClose" href="#">{t _close}</a>
<h1>{t _your_basket}</h1>

{include file="order/changeMessages.tpl"}

{if !$cart.cartItems}
	<div class="emptyBasket">
		{t _empty_basket}. <a href="{link route=$return}">{t _continue_shopping}</a>.
	</div>
{else}
	{include file="order/cartItems.tpl"}
{/if}

<script type="text/javascript">
	new Order.OptionLoader($('cart'));
</script>

