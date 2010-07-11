<h2><span class="step">{$steps.shippingMethod}</span>{t _select_shipping}</h2>
{form action="controller=onePageCheckout action=doSelectShippingMethod" method="POST" handle=$form}
	{foreach from=$shipments key="key" item="shipment"}

		{if $shipment.isShippable}
			{if $order.isMultiAddress}
				<h2>{$shipment.ShippingAddress.compact}</h2>
			{/if}

			{if $shipments|@count > 1}
				{include file="checkout/shipmentProductList.tpl"}
			{/if}

			{if $rates.$key}
				{include file="checkout/block/shipmentSelectShippingRateFields.tpl"}
			{else}
				<span class="errorText">{t _err_no_rates_for_address}</span>
			{/if}
		{/if}
	{/foreach}

	{if !$shipments}
		<span class="errorText">{t _err_no_rates_for_address}</span>
	{/if}
{/form}

<div class="notAvailable">
	<p>{t _no_shipping_address_provided}</p>
</div>