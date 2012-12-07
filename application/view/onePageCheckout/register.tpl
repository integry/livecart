{form action="controller=onePageCheckout action=doSelectBillingAddress" method="POST" handle=$form}
	{include file="user/block/registerAddress.tpl" prefix="billing_"}
	{include file="checkout/orderFields.tpl" eavPrefix="order_"}
	<input type="hidden" name="sameAsShipping" />
	{include file="onePageCheckout/block/continueButton.tpl"}
{/form}
