<div class="accordion-group">
	<div class="stepTitle accordion-heading">
		[[ partial('onePageCheckout/block/title.tpl', ['title': "_select_shipping"]) ]]
	</div>

	<div class="accordion-body">
		<div class="accordion-inner">
			<div class="form">
				{form action="onePageCheckout/doSelectShippingMethod" method="POST" handle=$form}
					{foreach from=$shipments key="key" item="shipment"}

						{% if $shipment.isShippable %}
							{% if $order.isMultiAddress %}
								<h2>[[shipment.ShippingAddress.compact]]</h2>
							{% endif %}

							{% if $shipments|@count > 1 %}
								[[ partial("checkout/shipmentProductList.tpl") ]]
							{% endif %}

							{% if $rates.$key %}
								[[ partial("checkout/block/shipmentSelectShippingRateFields.tpl") ]]
							{% else %}
								<span class="text-danger">{t _err_no_rates_for_address}</span>
							{% endif %}
						{% endif %}
					{/foreach}

					[[ partial("onePageCheckout/block/continueButton.tpl") ]]
				{/form}
				{% if empty(shipments) %}
					<div class="text-danger">{t _err_no_rates_for_address}</div>
				{% endif %}
			</div>

			<div class="notAvailable">
				<p>{t _no_shipping_address_provided}</p>
			</div>
		</div>

		{% if !empty(preview_shipping_methods) %}
			<div class="stepPreview">
			{foreach from=$preview_shipping_methods item=method}
				<div class="shippingPreview">
					[[method.ShippingService.name_lang]]
					({$method.formattedPrice[$method.costCurrency]})
				</div>
			{/foreach}
			</div>
		{% endif %}
	</div>
</div>
