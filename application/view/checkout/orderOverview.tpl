<div class="orderOverviewControls">
	<a href="{link controller=order}">{t _any_changes}</a>
</div>
<table class="table shipment" id="payItems">
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
		{include file="order/orderTableDetails.tpl" hideTaxes=true}
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