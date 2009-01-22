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

			{if !$billingAddresses}
				<div id="billingAddressForm">
					{include file="user/addressForm.tpl" prefix="billing_" states=$billing_states}
				</div>
			{else}
				<table class="addressSelector">
					{foreach from=$billingAddresses item="item"}
						<tr>
							<td class="selector">
								{radio class="radio" name="billingAddress" id="billing_`$item.UserAddress.ID`" value=$item.UserAddress.ID}
							</td>
							<td class="address" onclick="$('billing_{$item.UserAddress.ID}').checked = true; $('billing_{$item.UserAddress.ID}').form.onchange();">
									{include file="user/address.tpl"}
									<a href="{link controller=user action=editBillingAddress id=$item.ID returnPath=true}">{t _edit_address}</a>
							</td>
						</tr>
					{/foreach}
					<tr>
						<td class="selector addAddress">
							{radio class="radio" name="billingAddress" id="billing_new" value=""}
						</td>
						<td class="address addAddress">
							<label for="billing_new" class="radio">{t _new_billing_address}</label>
							<div class="address">
								<div class="addressBlock">
									{include file="user/addressForm.tpl" prefix="billing_" states=$billing_states}
								</div>
							</div>
						</td>
					</tr>
				</table>
			{/if}

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

			{if !$shippingAddresses}
				<div id="shippingAddressForm">
					{include file="user/addressForm.tpl" prefix="shipping_" states=$shipping_states}
				</div>
			{else}
				<table class="addressSelector">
					{foreach from=$shippingAddresses item="item"}
						<tr>
							<td class="selector">
								{radio class="radio" name="shippingAddress" id="shipping_`$item.UserAddress.ID`" value=$item.UserAddress.ID}
							</td>
							<td class="address" onclick="$('shipping_{$item.UserAddress.ID}').checked = true;">
								{include file="user/address.tpl"}
								<a href="{link controller=user action=editShippingAddress id=$item.ID returnPath=true}">{t _edit_address}</a>
							</td>
						</tr>
					{/foreach}
					<tr>
						<td class="selector addAddress">
							{radio class="radio" name="shippingAddress" id="shipping_new" value=""}
						</td>
						<td class="address addAddress">
							<label for="shipping_new" class="radio">{t _new_shipping_address}</label>
							<div class="address">
								<div class="addressBlock">
									{include file="user/addressForm.tpl" prefix="shipping_" states=$shipping_states}
								</div>
							</div>
						</td>
					</tr>
				</table>
			{/if}

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