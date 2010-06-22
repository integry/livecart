{loadJs form=true}

{include file="checkout/layout.tpl"}

<div id="content" class="left orderIndex">
	<h1>{t _checkout}</h1>

	<div id="checkout-cart">
		{$cart}
	</div>

	<div id="checkout-overview">
		{$overview}
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

	</div>

</div>

{include file="layout/frontend/footer.tpl"}
