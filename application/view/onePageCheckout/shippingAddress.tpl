<div class="accordion-group">
	<div class="stepTitle accordion-heading">
		[[ partial('onePageCheckout/block/title.tpl', ['title': "_shipping_address"]) ]]
	</div>

	<div class="accordion-body">
		<div class="accordion-inner">
			{form action="onePageCheckout/doSelectShippingAddress" method="POST" handle=form class="form-vertical"}
				{% if !order.isMultiAddress %}
					<div class="checkbox">
						<label>
							{checkbox name="sameAsBilling" id="sameAsBilling"}
							{t _the_same_as_billing_address}
						</label>
					</div>
				{% endif %}

				[[ partial('checkout/block/selectAddress.tpl', ['addresses': shippingAddresses, 'prefix': "shipping", 'states': shippingStates]) ]]

				[[ partial("onePageCheckout/block/continueButton.tpl") ]]
			{/form}
		</div>

		{% if !empty(preview_shipping) %}
			<div class="stepPreview">[[preview_shipping.compact]]</div>
		{% endif %}
	</div>
</div>