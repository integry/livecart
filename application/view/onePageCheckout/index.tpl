{% extends "layout/frontend.tpl" %}

{includeJs file="frontend/OnePageCheckout.js"}
{% block title %}{t _checkout}{{% endblock %}

<noscript>
	<meta http-equiv="refresh" content="0;[[ url("onePageCheckout/fallback") ]]" />
</noscript>

<script type="text/javascript">
	if (Prototype.Browser.IE6)
	{ldelim}
		window.location.href = '[[ url("onePageCheckout/fallback") ]]';
	{rdelim}
</script>

[[ partial("checkout/layout.tpl") ]]
{% block content %}

<div class="row">

	<div class="col col-lg-8" id="checkout-left">

	{% if !$user.ID %}
	<div id="checkout-login" class="step">
		[[login]]
	</div>
	{% endif %}

	<div class="accordion">

		<div id="checkout-billing" class="step">
			[[billingAddress]]
		</div>

		<div id="checkout-shipping">
			<div id="checkout-shipping-address" class="step">
				[[shippingAddress]]
			</div>
			<div id="checkout-shipping-method" class="step">
				[[shippingMethods]]
			</div>
		</div>

		<div id="checkout-payment" class="step">
			[[payment]]
		</div>

	</div>

	</div>

	<div class="col col-lg-4" id="checkout-right">
		<div id="checkout-right-inner">
			<div id="checkout-cart">
				[[cart]]
			</div>

			<div id="checkout-overview">
				[[overview]]
			</div>
		</div>
	</div>

</div>

{% endblock %}


	<script type="text/javascript">
		var checkout = new Frontend.OnePageCheckout({ldelim}OPC_SHOW_CART: [[ config('OPC_SHOW_CART') ]]{rdelim});
		checkout.updateCompletedSteps({json array=$completedSteps});
		checkout.updateEditableSteps({json array=$editableSteps});
		Observer.process('order', {json array=$orderValues});

		new User.ShippingFormToggler($('sameAsShipping'), $('billingAddressForm'));
		new User.ShippingFormToggler($('sameAsShipping'), $('checkout-billing').down('.addressSelector'));
	</script>
