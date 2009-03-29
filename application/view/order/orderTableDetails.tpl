{assign var="colspan" value=$colspan|@or:4}

{foreach from=$shipment.items item="item" name="shipment"}
	<tr class="{zebra loop="shipment"}{if $productsInSeparateLine} topLine{/if}">
		<td class="sku">
			{$item.Product.sku}
		</td>
		{if !$productsInSeparateLine}
			<td class="productName">
				{include file="order/itemProductInfo.tpl"}
			</td>
		{/if}
		<td class="{if $item.itemBasePrice > $item.itemPrice}discount{/if}">
			<span class="basePrice">{$item.formattedDisplayPrice}</span><span class="actualPrice">{$item.formattedPrice}</span>
		</td>
		<td>{$item.count}</td>
		<td class="amount">{$item.formattedDisplaySubTotal}</td>
	</tr>
	{if $productsInSeparateLine}
		<tr class="{zebra loop="shipment" stop=true}">
			<td class="productName" colspan="4">
				{include file="order/itemProductInfo.tpl"}
			</td>
		</tr>
	{/if}
{/foreach}

{if $shipment.taxes && !$hideTaxes}
	<tr>
		<td colspan="{$colspan}" class="subTotalCaption beforeTax">{t _subtotal_before_tax}:</td>
		<td class="amount">{$shipment.formatted_amount}</td>
	</tr>
{/if}

{if $order.isShippingRequired && $shipment.isShippable && $shipment.ShippingService}
	<tr class="overviewShippingInfo">
		<td colspan="{$colspan}" class="subTotalCaption">
			{t _shipping} ({$shipment.ShippingService.name_lang}):
		</td>
		<td>
			{$shipment.selectedRate.formattedPrice[$order.Currency.ID]}
		</td>
	</tr>
{/if}