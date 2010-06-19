{form action="controller=checkout action=doSelectShippingMethod" method="POST" handle=$form}
	{foreach from=$shipments key="key" item="shipment"}

		{if $order.isMultiAddress}
			<h2>{$shipment.ShippingAddress.compact}</h2>
		{/if}

		{if $shipments|@count > 1}
			{include file="checkout/shipmentProductList.tpl"}
		{/if}

		{if $shipment.isShippable}
			{include file="checkout/shipmentSelectShipping.tpl"}

		{/if}

	{/foreach}

	<p>
		<input type="submit" class="submit" value="{tn _continue}" />
	</p>

{/form}
