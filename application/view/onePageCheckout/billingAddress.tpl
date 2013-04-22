<div class="stepTitle">
	{include file="onePageCheckout/block/modifyStep.tpl"}
	<h2><span class="step">{$steps.billingAddress}</span>{t _billing_address}</h2>
</div>

{if $user.ID > 0}
	{form action="controller=onePageCheckout action=doSelectBillingAddress" method="POST" handle=$form class="form-horizontal"}
		{include file="checkout/block/selectAddress.tpl" addresses=$billingAddresses prefix="billing" states=$billing_states}
		{include file="checkout/orderFields.tpl"}
		{include file="onePageCheckout/block/continueButton.tpl"}
	{/form}
{else}
	{include file="onePageCheckout/register.tpl" states=$billing_states}
{/if}

{if $preview_billing}
	<div class="stepPreview">{$preview_billing.compact}</div>
{/if}