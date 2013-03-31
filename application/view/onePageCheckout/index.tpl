{loadJs form=true}
{pagetitle}{t _checkout}{/pageTitle}

<noscript>
	<meta http-equiv="refresh" content="0;{link controller=onePageCheckout action=fallback}" />
</noscript>

<script type="text/javascript">
	if (Prototype.Browser.IE6)
	{ldelim}
		window.location.href = '{link controller=onePageCheckout action=fallback}';
	{rdelim}
</script>

{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

	<div id="checkout-right">
		<div id="checkout-cart">
			{$cart}
		</div>

		<div id="checkout-overview">
			{$overview}
		</div>
	</div>

	{if !$user.ID}
	<div id="checkout-login" class="step">
		{$login}
	</div>
	{/if}

	<div id="checkout-billing" class="step">
		{$billingAddress}
	</div>

	<div id="checkout-shipping">
		<div id="checkout-shipping-address" class="step">
			{$shippingAddress}
		</div>
		<div id="checkout-shipping-method" class="step">
			{$shippingMethods}
		</div>
	</div>

	<div id="checkout-payment" class="step">
		{$payment}
	</div>

	<div class="clear"></div>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}

	<script type="text/javascript">
		var checkout = new Frontend.OnePageCheckout({ldelim}OPC_SHOW_CART: {'OPC_SHOW_CART'|config}{rdelim});
		checkout.updateCompletedSteps({json array=$completedSteps});
		checkout.updateEditableSteps({json array=$editableSteps});
		Observer.process('order', {json array=$orderValues});
{literal}
		new User.ShippingFormToggler($('sameAsShipping'), $('billingAddressForm'));
		new User.ShippingFormToggler($('sameAsShipping'), $('checkout-billing').down('.addressSelector'));
	</script>
{/literal}