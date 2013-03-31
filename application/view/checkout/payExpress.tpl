{loadJs form=true}
{pageTitle}{t _pay}{/pageTitle}
{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

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

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}