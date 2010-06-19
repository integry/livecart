<h2>{t _billing_address}</h2>

{form action="controller=onePageCheckout action=doSelectBillingAddress" method="POST" handle=$form}
	{if !$order.isMultiAddress}
		<p>
			{checkbox name="sameAsBilling" class="checkbox"}
			<label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
		</p>
	{/if}

	{include file="checkout/block/selectAddress.tpl" addresses=$billingAddresses prefix="billing" states=$billing_states}
{/form}
