<div class="orderOverviewControls">
	<a href="{link controller=order}">{t _any_changes}</a>
</div>
<table class="table shipment{if $order.isMultiAddress} multiAddress{/if}" id="payItems">
	<thead>
		<tr>
			<th class="productName">{t _product}</th>
			<th>{t _price}</th>
			<th>{t _quantity}</th>
			<th>{t _subtotal}</th>
		</tr>
	</thead>
	<tbody>

	{foreach from=$order.shipments key="key" item="shipment"}
		{if $order.isMultiAddress}
			<tr>
				<td colspan="4" class="shipmentAddress">
					{$shipment.ShippingAddress.compact}
				</td>
			</tr>
		{/if}
		{include file="order/orderTableDetails.tpl" hideTaxes=true}
	{/foreach}

	{foreach from=$order.discounts item=discount}
		<tr>
			<td colspan="3" class="subTotalCaption">{t _discount}: <span class="discountDesc">{$discount.description}</span></td>
			<td class="amount discountAmount">{$discount.formatted_amount}</td>
		</tr>
	{/foreach}

  	{if $order.taxes}
		<tr>
			<td colspan="3" class="tax">{t _total_before_tax}:</td>
			<td>{$order.formattedTotalBeforeTax.$currency}</td>
		</tr>
  	{/if}

	{foreach from=$order.taxes.$currency item="tax"}
		<tr>
			<td colspan="3" class="tax">{$tax.name_lang}:</td>
			<td>{$tax.formattedAmount}</td>
		</tr>
	{/foreach}

	<tr>
		<td colspan="3" class="subTotalCaption">{t _total}:</td>
		<td class="subTotal">{$order.formattedTotal.$currency}</td>
	</tr>

	</tbody>
</table>

{include file="order/fieldValues.tpl"}