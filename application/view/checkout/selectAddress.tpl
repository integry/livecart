{% extends "layout/frontend.tpl" %}

{% title %}{t _select_addresses}{% endblock %}
[[ partial("checkout/layout.tpl") ]]

<div class="step-[[step]]">
{% block content %}

	<div class="checkoutHeader">
		{% if 'shipping' == $step %}
			[[ partial('checkout/checkoutProgress.tpl', ['progress': "progressShippingAddress"]) ]]
		{% else %}
			[[ partial('checkout/checkoutProgress.tpl', ['progress': "progressAddress"]) ]]
		{% endif %}
	</div>

	{form action="checkout/doSelectAddress" method="POST" handle=$form  class="form-horizontal"}

	{error for="selectedAddress"}<div><span class="text-danger">[[msg]]</span></div><div class="clear"></div>{/error}

	{% if !$step || ('billing' == $step) %}
		<div id="billingAddressColumn">

			{% if !'REQUIRE_SAME_ADDRESS'|config %}
				<h2 id="billingAddress">{t _billing_address}</h2>
			{% endif %}

			[[ partial('checkout/block/selectAddress.tpl', ['addresses': billingAddresses, 'prefix': "billing", 'states': billing_states]) ]]

			{% if !'REQUIRE_SAME_ADDRESS'|config && $order.isShippingRequired && !$order.isMultiAddress && !$step %}
				<p>
					{checkbox name="sameAsBilling" class="checkbox"}
					<label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
				</p>
			{% endif %}

		</div>
	{% endif %}

	{% if (!'REQUIRE_SAME_ADDRESS'|config && $order.isShippingRequired && !$order.isMultiAddress) && (!$step || ('shipping' == $step)) %}

		{% if 'shipping' == $step %}
			<div class="clear"></div>
		{% endif %}

		<div id="shippingSelector">

			<h2 id="shippingAddress">{t _shipping_address}</h2>

			[[ partial('checkout/block/selectAddress.tpl', ['addresses': shippingAddresses, 'prefix': "shipping", 'states': shipping_states]) ]]

		</div>


		<script type="text/javascript">
			new User.ShippingFormToggler($('sameAsBilling'), $('shippingSelector'));
		</script>


	{% endif %}

	{% if (('BILLING_ADDRESS_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config) && !$step) || (('SHIPPING_ADDRESS_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config) && (('shipping' == $step) || !'ENABLE_CHECKOUTDELIVERYSTEP'|config || !$order.isShippingRequired)) || 'REQUIRE_SAME_ADDRESS'|config %}
		<div class="clear"></div>
		[[ partial("checkout/orderFields.tpl") ]]
	{% endif %}


	<script type="text/javascript">
		new Order.AddressSelector($('content'));
	</script>


	[[ partial('block/submit.tpl', ['caption': "_continue"]) ]]

	<input type="hidden" name="step" value="[[step]]" />

	{/form}

{% endblock %}

