{if $order.isMultiAddress}
	<div class="shipmentAddress">
		<span class="shipmentAddressLabel">{t _shipment_shipped_to}:</span> {$shipment.ShippingAddress.compact}
	</div>
{/if}

<table class="table shipment">

	<thead>
		<tr>
			<th class="sku">{t _sku}</th>
			<th class="productName">{t _product}</th>
			<th>{t _price}</th>
			<th>{t _quantity}</th>
			<th>{t _subtotal}</th>
		</tr>
	</thead>

	<tbody>

		{include file="order/orderTableDetails.tpl"}

		{if !'HIDE_TAXES'|config || $showTaxes}
			{foreach from=$shipment.taxes item="tax"}
				<tr>
					<td colspan="4" class="tax">{$tax.TaxRate.Tax.name_lang}:</td>
					<td>{$tax.formattedAmount[$order.Currency.ID]}</td>
				</tr>
			{/foreach}
		{/if}

		{if $smarty.foreach.shipments.iteration == 1}
			{foreach from=$order.discounts item=discount}
				{if $discount.amount != 0}
					<tr>
						<td colspan="4" class="subTotalCaption">{if $discount.amount > 0}{t _discount}{else}{t _surcharge}{/if}: <span class="discountDesc">{$discount.description}</span></td>
						<td class="amount discountAmount">{$discount.formatted_amount}</td>
					</tr>
				{/if}
			{/foreach}
		{/if}

		<tr>
			<td colspan="4" class="subTotalCaption">
				{if $smarty.foreach.shipments.total > 1}
					{t _shipment_total}:
				{else}
					{t _order_total}:
				{/if}
			</td>
			<td class="subTotal">
				{if $smarty.foreach.shipments.total == 1}
					{$order.formattedTotal[$order.Currency.ID]}
				{else}
					{$shipment.formatted_totalAmount}
				{/if}
			</td>
		</tr>

	</tbody>

</table>