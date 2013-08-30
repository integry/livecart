{form action="onePageCheckout/doSelectBillingAddress" method="POST" handle=$form class="form-horizontal"}
	{include file="user/block/registerAddress.tpl" prefix="billing_"}
	{include file="checkout/orderFields.tpl" eavPrefix="order_"}
	<input type="hidden" name="sameAsShipping" />
	[[ partial("onePageCheckout/block/continueButton.tpl") ]]
{/form}
