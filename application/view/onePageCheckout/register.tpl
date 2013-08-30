{form action="onePageCheckout/doSelectBillingAddress" method="POST" handle=$form class="form-horizontal"}
	[[ partial('user/block/registerAddress.tpl', ['prefix': "billing_"]) ]]
	[[ partial('checkout/orderFields.tpl', ['eavPrefix': "order_"]) ]]
	<input type="hidden" name="sameAsShipping" />
	[[ partial("onePageCheckout/block/continueButton.tpl") ]]
{/form}
