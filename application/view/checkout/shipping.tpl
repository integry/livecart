{loadJs form=true}
{pageTitle}{t _shipping}{/pageTitle}
{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

	<div class="checkoutHeader">
		{include file="checkout/checkoutProgress.tpl" progress="progressShipping"}
	</div>

	{if $shipments|@count > 1 && !$order.isMultiAddress}
		<div class="infoMessage">
			{t _info_multi_shipments}
		</div>
	{/if}

	<div id="shippingSelect">

		{form action="controller=checkout action=doSelectShippingMethod" method="POST" handle=$form class="form-horizontal"}
			{foreach from=$shipments key="key" item="shipment"}

				{if $order.isMultiAddress}
					<h2>[[shipment.ShippingAddress.compact]]</h2>
				{/if}

				{include file="checkout/shipmentProductList.tpl"}

				{if $shipment.isShippable}
					{include file="checkout/shipmentSelectShipping.tpl"}

				{/if}

			{/foreach}

		{if 'SHIPPING_METHOD_STEP' == 'CHECKOUT_CUSTOM_FIELDS'|config}
			{include file="checkout/orderFields.tpl"}
		{/if}

		{include file="block/submit.tpl" caption="_continue"}

		{/form}

	</div>

{include file="block/content-stop.tpl"}
{include file="layout/frontend/footer.tpl"}