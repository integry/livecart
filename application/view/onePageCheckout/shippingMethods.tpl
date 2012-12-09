<div class="stepTitle">
	{include file="onePageCheckout/block/modifyStep.tpl"}
	<h2><span class="step">{$steps.shippingMethod}</span>{t _select_shipping}</h2>
</div>

<div class="form">
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

		{include file="onePageCheckout/block/continueButton.tpl"}
	{/form}
	{if !$shipments}
		<div class="errorText">{t _err_no_rates_for_address}</div>
	{/if}
</div>

<div class="notAvailable">
	<p>{t _no_shipping_address_provided}</p>
</div>

{if $preview_shipping_methods}
	<div class="stepPreview">
	{foreach from=$preview_shipping_methods item=method}
		<div class="shippingPreview">
			{$method.ShippingService.name_lang}
			({$method.formattedPrice[$method.costCurrency]})
		</div>
	{/foreach}
	</div>
{/if}