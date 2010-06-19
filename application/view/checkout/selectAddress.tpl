<div class="checkoutSelectAddress">
{loadJs form=true}

{include file="checkout/layout.tpl"}

<div id="content" class="left right step-{$step}">

	<div class="checkoutHeader">
		<h1>{t _select_addresses}</h1>

		{if 'shipping' == $step}
			{include file="checkout/checkoutProgress.tpl" progress="progressShippingAddress"}
		{else}
			{include file="checkout/checkoutProgress.tpl" progress="progressAddress"}
		{/if}
	</div>

	{form action="controller=checkout action=doSelectAddress" method="POST" handle=$form style="display: block; width: 100%;"}

	{error for="selectedAddress"}<div><span class="errorText">{$msg}</span></div><div class="clear"></div>{/error}

	{if !$step || ('billing' == $step)}
		<fieldset class="container" id="billingAddressColumn">
			<h2 id="billingAddress">{t _billing_address}</h2>
			
			{include file="checkout/block/selectAddress.tpl" addresses=$billingAddresses prefix="billing" states=$billing_states}

			{if $order.isShippingRequired && !$order.isMultiAddress && !$step}
				<p>
					{checkbox name="sameAsBilling" class="checkbox"}
					<label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
				</p>
			{/if}

		</fieldset>
	{/if}

	{if ($order.isShippingRequired && !$order.isMultiAddress) && (!$step || ('shipping' == $step))}

		{if 'shipping' == $step}
			<div class="clear"></div>
		{/if}

		<fieldset class="container" id="shippingSelector">

			<h2 id="shippingAddress">{t _shipping_address}</h2>
			
			{include file="checkout/block/selectAddress.tpl" addresses=$shippingAddresses prefix="shipping" states=$shipping_states}

		</fieldset>

		{literal}
		<script type="text/javascript">
			new User.ShippingFormToggler($('sameAsBilling'), $('shippingSelector'));
		</script>
		{/literal}

	{/if}

	{if (('BILLING_ADDRESS_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config) && !$step) || (('SHIPPING_ADDRESS_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config) && (('shipping' == $step) || !'ENABLE_CHECKOUTDELIVERYSTEP'|config))}
		<div class="clear"></div>
		{include file="checkout/orderFields.tpl"}
	{/if}

	{literal}
	<script type="text/javascript">
		new Order.AddressSelector($('content'));
	</script>
	{/literal}

	<div class="clear"></div>

	<p>
		<input type="hidden" name="step" value="{$step}" />
		<input type="submit" class="submit" value="{tn _continue}" />
	</p>

	{/form}

</div>

{include file="layout/frontend/footer.tpl"}

</div>
