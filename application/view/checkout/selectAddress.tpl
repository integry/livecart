{loadJs form=true}
{pageTitle}{t _select_addresses}{/pageTitle}
{include file="checkout/layout.tpl"}

<div class="step-{$step}">
{include file="block/content-start.tpl"}

	<div class="checkoutHeader">
		{if 'shipping' == $step}
			{include file="checkout/checkoutProgress.tpl" progress="progressShippingAddress"}
		{else}
			{include file="checkout/checkoutProgress.tpl" progress="progressAddress"}
		{/if}
	</div>

	{form action="controller=checkout action=doSelectAddress" method="POST" handle=$form  class="form-horizontal"}

	{error for="selectedAddress"}<div><span class="text-danger">{$msg}</span></div><div class="clear"></div>{/error}

	{if !$step || ('billing' == $step)}
		<fieldset class="container" id="billingAddressColumn">

			{if !'REQUIRE_SAME_ADDRESS'|config}
				<h2 id="billingAddress">{t _billing_address}</h2>
			{/if}

			{include file="checkout/block/selectAddress.tpl" addresses=$billingAddresses prefix="billing" states=$billing_states}

			{if !'REQUIRE_SAME_ADDRESS'|config && $order.isShippingRequired && !$order.isMultiAddress && !$step}
				<p>
					{checkbox name="sameAsBilling" class="checkbox"}
					<label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
				</p>
			{/if}

		</fieldset>
	{/if}

	{if (!'REQUIRE_SAME_ADDRESS'|config && $order.isShippingRequired && !$order.isMultiAddress) && (!$step || ('shipping' == $step))}

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

	{if (('BILLING_ADDRESS_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config) && !$step) || (('SHIPPING_ADDRESS_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config) && (('shipping' == $step) || !'ENABLE_CHECKOUTDELIVERYSTEP'|config || !$order.isShippingRequired)) || 'REQUIRE_SAME_ADDRESS'|config}
		<div class="clear"></div>
		{include file="checkout/orderFields.tpl"}
	{/if}

	{literal}
	<script type="text/javascript">
		new Order.AddressSelector($('content'));
	</script>
	{/literal}

	{include file="block/submit.tpl" caption="_continue"}

	<input type="hidden" name="step" value="{$step}" />

	{/form}

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}
