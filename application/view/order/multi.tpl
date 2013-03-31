{loadJs form=true}
{pageTitle}{t _select_shipping_addresses}{/pageTitle}
{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

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
		{include file="order/wishList.tpl"}
	{/if}

	{/if}

	<div class="clear"></div>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}

<script type="text/javascript">
	new Order.OptionLoader($('cart'));
</script>