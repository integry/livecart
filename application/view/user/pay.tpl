{pageTitle}{t _pay} #[[order.invoiceNumber]] ([[order.formatted_dateCompleted.date_long]]){/pageTitle}
{loadJs form=true}
{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

	<div id="payTotal">
		<div>
			{t _amount_due}: <span class="subTotal">[[order.formatted_amountDue]]</span>
		</div>
	</div>

	{include file="checkout/completeOverview.tpl" productsInSeparateLine=true}

	<div class="paymentMethods">
		{include file="checkout/paymentMethods.tpl"}
		{include file="checkout/offlinePaymentMethods.tpl"}
	</div>

	<div class="clear"></div>
{include file="block/content-stop.tpl"}

{include file="layout/frontend/footer.tpl"}

</div>
