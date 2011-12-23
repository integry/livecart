{form action="controller=onePageCheckout action=doSelectShippingAddress" method="POST" handle=$form}
	{include file="user/block/registerAddress.tpl" prefix="shipping_"}
	{include file="checkout/orderFields.tpl" eavPrefix="order_"}
	<input type="hidden" name="sameAsShipping" />

	<div class="confirmButtonContainer">
		<label class="confirmAddressLabel"></label>
		<input type="button" class="button confirmAddress" value="{tn _confirm_address}" />
	</div>
{/form}
