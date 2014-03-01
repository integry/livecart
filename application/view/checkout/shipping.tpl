{% extends "layout/frontend.tpl" %}

{% title %}{t _shipping}{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div class="checkoutHeader">
		[[ partial('checkout/checkoutProgress.tpl', ['progress': "progressShipping"]) ]]
	</div>

	{% if shipments|@count > 1 && !order.isMultiAddress %}
		<div class="infoMessage">
			{t _info_multi_shipments}
		</div>
	{% endif %}

	<div id="shippingSelect">

		{form action="checkout/doSelectShippingMethod" method="POST" handle=form class="form-horizontal"}
			{foreach from=shipments key="key" item="shipment"}

				{% if order.isMultiAddress %}
					<h2>[[shipment.ShippingAddress.compact]]</h2>
				{% endif %}

				[[ partial("checkout/shipmentProductList.tpl") ]]

				{% if shipment.isShippable %}
					[[ partial("checkout/shipmentSelectShipping.tpl") ]]

				{% endif %}

			{% endfor %}

		{% if 'SHIPPING_METHOD_STEP' == config('CHECKOUT_CUSTOM_FIELDS') %}
			[[ partial("checkout/orderFields.tpl") ]]
		{% endif %}

		[[ partial('block/submit.tpl', ['caption': "_continue"]) ]]

		{/form}

	</div>

{% endblock %}
