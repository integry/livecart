<h2>{t _select_shipping}</h2>
{form action="controller=checkout action=doSelectShippingMethod" method="POST" handle=$form}
	{foreach from=$shipments key="key" item="shipment"}

		{if $shipment.isShippable}
			{if $order.isMultiAddress}
				<h2>{$shipment.ShippingAddress.compact}</h2>
			{/if}

			{if $shipments|@count > 1}
				{include file="checkout/shipmentProductList.tpl"}
			{/if}

			{include file="checkout/block/shipmentSelectShippingRateFields.tpl"}
		{/if}

	{/foreach}

{/form}
