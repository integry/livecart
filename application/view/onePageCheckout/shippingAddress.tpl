{if $order.isShippingRequired}
	<h2>{t _shipping_address}</h2>
{else}
	<h2>{t _billing_address}</h2>
{/if}

{if $user.ID > 0}
	{form action="controller=onePageCheckout action=doSelectShippingAddress" method="POST" handle=$form}
		{include file="checkout/block/selectAddress.tpl" addresses=$shippingAddresses prefix="shipping" states=$shipping_states}
	{/form}
{else}
	{include file="onePageCheckout/register.tpl"}
{/if}
