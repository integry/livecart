{loadJs form=true}

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

<div id="content" class="left orderIndex">
	<h1>{t _checkout}</h1>

	<div id="checkout-right">
		<div id="checkout-cart">
			{$cart}
		</div>

		<div id="checkout-overview">
			{$overview}
		</div>
	</div>

	<div id="checkout-login">
		{$login}
	</div>

	<div id="checkout-shipping">
		<div id="checkout-shipping-address">
			{$shippingAddress}
		</div>
		<div id="checkout-shipping-method">
			{$shippingMethods}
		</div>
	</div>

	<div id="checkout-billing">
		{$billingAddress}
	</div>

	<div id="checkout-payment">
		{$payment}
	</div>

</div>
	<script type="text/javascript">
		var checkout = new Frontend.OnePageCheckout();
		checkout.updateCompletedSteps({json array=$completedSteps});
		checkout.updateCompletedSteps({json array=$completedSteps});
		checkout.updateEditableSteps({json array=$editableSteps});
		Observer.process('order', {json array=$orderValues});
{literal}
		new User.ShippingFormToggler($('sameAsShipping'), $('billingAddressForm'));
	</script>
{/literal}

{include file="layout/frontend/footer.tpl"}
