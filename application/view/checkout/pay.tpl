<div class="checkoutPay">

{loadJs form=true}
{include file="checkout/layout.tpl"}

<div id="content" class="left right">

	<div class="checkoutHeader">
		<h1>{t _pay}</h1>

		{include file="checkout/checkoutProgress.tpl" progress="progressPayment"}
	</div>

	<div id="payTotal">
		<div>
			{t _order_total}: <span class="subTotal">{$order.formattedTotal.$currency}</span>
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