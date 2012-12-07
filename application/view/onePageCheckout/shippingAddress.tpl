<div class="stepTitle">
	{include file="onePageCheckout/block/modifyStep.tpl"}
	<h2><span class="step">{$steps.shippingAddress}</span>{t _shipping_address}</h2>
</div>

{form action="controller=onePageCheckout action=doSelectShippingAddress" method="POST" handle=$form}

	{if !$order.isMultiAddress}
		<p>
			{checkbox name="sameAsBilling" id="sameAsBilling" class="checkbox"}
			<label for="sameAsBilling" class="checkbox">{t _the_same_as_billing_address}</label>
		</p>
	{/if}

	{include file="checkout/block/selectAddress.tpl" addresses=$shippingAddresses prefix="shipping" states=$shippingStates}

	{include file="onePageCheckout/block/continueButton.tpl"}
{/form}

{if $preview_shipping}
	<div class="stepPreview">{$preview_shipping.compact}</div>
{/if}