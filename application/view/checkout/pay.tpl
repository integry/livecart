{loadJs form=true}
{pageTitle}{t _pay}{/pageTitle}
{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

	<div class="checkoutHeader">
		{include file="checkout/checkoutProgress.tpl" progress="progressPayment"}
	</div>

	<div id="payTotal">
		<div>
			{t _order_total}: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
	</div>

	{if $error}
		<div class="errorMessage">
			<div>[[error]]</div>
		</div>
	{/if}

	{include file="checkout/completeOverview.tpl" productsInSeparateLine=true}

	<div class="paymentMethods">
		{include file="checkout/paymentMethods.tpl"}
		{include file="checkout/offlinePaymentMethods.tpl"}
	</div>

	<div class="clear"></div>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}