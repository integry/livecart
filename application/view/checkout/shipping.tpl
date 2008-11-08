<div class="checkoutShipping">

{loadJs form=true}
{include file="checkout/layout.tpl"}

<div id="content" class="left right checkoutShipping">

	<div class="checkoutHeader">
		<h1>{t _shipping}</h1>

		{include file="checkout/checkoutProgress.tpl" progress="progressShipping"}
	</div>

	{if $shipments|@count > 1 && !$order.isMultiAddress}
		<div class="infoMessage">
			{t _info_multi_shipments}
		</div>
	{/if}

	<div id="shippingSelect">

	{form action="controller=checkout action=doSelectShippingMethod" method="POST" handle=$form}
		{foreach from=$shipments key="key" item="shipment"}

			{if $order.isMultiAddress}
				<h2>{$shipment.ShippingAddress.compact}</h2>
			{/if}

			{include file="checkout/shipmentProductList.tpl"}

			{if $shipment.isShippable}
				{include file="checkout/shipmentSelectShipping.tpl"}

			{/if}

		{/foreach}

	<input type="submit" class="submit" value="{tn _continue}" />

	{/form}

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>