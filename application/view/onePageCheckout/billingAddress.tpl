<div class="accordion-group">
	<div class="stepTitle accordion-heading">
		[[ partial('onePageCheckout/block/title.tpl', ['title': "_billing_address"]) ]]
	</div>

	<div class="accordion-body">
		<div class="accordion-inner">
			{% if $user.ID > 0 %}
				{form action="onePageCheckout/doSelectBillingAddress" method="POST" handle=$form class="form-vertical"}
					[[ partial('checkout/block/selectAddress.tpl', ['addresses': $billingAddresses, 'prefix': "billing", 'states': $billing_states]) ]]
					[[ partial("checkout/orderFields.tpl") ]]
					[[ partial("onePageCheckout/block/continueButton.tpl") ]]
				{/form}
			{% else %}
				[[ partial('onePageCheckout/register.tpl', ['states': $billing_states]) ]]
			{% endif %}
		</div>
		{% if $preview_billing %}
			<div class="stepPreview">[[preview_billing.compact]]</div>
		{% endif %}
	</div>
</div>