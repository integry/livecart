<h2><span class="step">{$steps.billingAddress}</span>{t _billing_address}</h2>

{form action="controller=onePageCheckout action=doSelectBillingAddress" method="POST" handle=$form}
	{if !$order.isMultiAddress}
		<p>
			{checkbox name="sameAsShipping" class="checkbox"}
			<label for="sameAsShipping" class="checkbox">{t _the_same_as_shipping_address}</label>
		</p>
	{/if}

	{include file="checkout/block/selectAddress.tpl" confirmButton=true addresses=$billingAddresses prefix="billing" states=$billing_states}
{/form}

<div class="notAvailable">
	<p>{t _please_enter_shipping_address}</p>
</div>