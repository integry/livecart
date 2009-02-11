<div class="userPay">

{loadJs form=true}
{include file="checkout/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _pay} #{$order.ID} ({$order.formatted_dateCompleted.date_long})</h1>

	<div id="payTotal">
		<div>
			{t _amount_due}: <span class="subTotal">{$order.formatted_amountDue}</span>
		</div>
	</div>

	{include file="checkout/completeOverview.tpl" productsInSeparateLine=true}

	<div class="paymentMethods">
		{include file="checkout/paymentMethods.tpl"}
		{include file="checkout/offlinePaymentMethods.tpl"}
	</div>

	<div class="clear"></div>
</div>

{include file="layout/frontend/footer.tpl"}

</div>