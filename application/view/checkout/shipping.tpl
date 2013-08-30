{% extends "layout/frontend.tpl" %}

{% block title %}{t _shipping}{{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div class="checkoutHeader">
		{include file="checkout/checkoutProgress.tpl" progress="progressShipping"}
	</div>

	{if $shipments|@count > 1 && !$order.isMultiAddress}
		<div class="infoMessage">
			{t _info_multi_shipments}
		</div>
	{/if}

	<div id="shippingSelect">

		{form action="checkout/doSelectShippingMethod" method="POST" handle=$form class="form-horizontal"}
			{foreach from=$shipments key="key" item="shipment"}

				{if $order.isMultiAddress}
					<h2>[[shipment.ShippingAddress.compact]]</h2>
				{/if}

				[[ partial("checkout/shipmentProductList.tpl") ]]

				{if $shipment.isShippable}
					[[ partial("checkout/shipmentSelectShipping.tpl") ]]

				{/if}

			{/foreach}

		{if 'SHIPPING_METHOD_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config}
			[[ partial("checkout/orderFields.tpl") ]]
		{/if}

		{include file="block/submit.tpl" caption="_continue"}

		{/form}

	</div>

{% endblock %}
