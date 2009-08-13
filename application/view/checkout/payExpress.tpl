<div class="checkoutPay">

{loadJs form=true}

{include file="checkout/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _pay}</h1>

	<div id="payTotal">
		<div>
			Order total: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
	</div>

	<div class="clear"></div>

	<form action="{link controller=checkout action=payExpressComplete}" method="post" id="expressComplete">

		<input type="submit" class="submit" value="{tn Complete Your Order}" />

	</form>

	<div class="clear"></div>

	{include file="checkout/orderOverview.tpl"}

</div>

{include file="layout/frontend/footer.tpl"}

</div>