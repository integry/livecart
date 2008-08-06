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

	{include file="checkout/paymentMethods.tpl"}

	{if 'OFFLINE_PAYMENT'|config}
		<h2>{t _pay_offline}</h2>

		{include file="checkout/offlinePaymentInfo.tpl"}

	{/if}

	<h2>{t _order_overview}</h2>

	{include file="checkout/orderOverview.tpl"}

</div>

{include file="layout/frontend/footer.tpl"}

</div>