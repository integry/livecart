<div class="accordion-group">
	<div class="stepTitle accordion-heading">
		{include file="onePageCheckout/block/title.tpl" title="_billing_address"}
	</div>

	<div class="accordion-body">
		<div class="accordion-inner">
			{if $user.ID > 0}
				{form action="controller=onePageCheckout action=doSelectBillingAddress" method="POST" handle=$form class="form-vertical"}
					{include file="checkout/block/selectAddress.tpl" addresses=$billingAddresses prefix="billing" states=$billing_states}
					{include file="checkout/orderFields.tpl"}
					{include file="onePageCheckout/block/continueButton.tpl"}
				{/form}
			{else}
				{include file="onePageCheckout/register.tpl" states=$billing_states}
			{/if}
		</div>
		{if $preview_billing}
			<div class="stepPreview">{$preview_billing.compact}</div>
		{/if}
	</div>
</div>