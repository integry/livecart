<table class="table shipment">

	<thead>
		<tr>
			<th class="productName">{t _product}</th>
			<th>{t _price}</th>
			<th>{t _quantity}</th>
			<th>{t _subtotal}</th>
		</tr>
	</thead>

	<tbody>

		{include file="order/orderTableDetails.tpl"}

		{foreach from=$shipment.taxes item="tax"}
			<tr>
				<td colspan="3" class="tax">{$tax.TaxRate.Tax.name_lang}:</td>
				<td>{$tax.formattedAmount[$order.Currency.ID]}</td>
			</tr>
		{/foreach}

		<tr>
			<td colspan="3" class="subTotalCaption">
				{if $smarty.foreach.shipments.total > 1}
					{t _shipment_total}:
				{else}
					{t _order_total}:
				{/if}
			</td>
			<td class="subTotal">{$shipment.formatted_totalAmount}</td>
		</tr>

	</tbody>

</table>