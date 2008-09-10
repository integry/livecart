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

		{if $smarty.foreach.shipments.iteration == 1}
			{foreach from=$order.discounts item=discount}
				<tr>
					<td colspan="3" class="subTotalCaption">{t _discount}: <span class="discountDesc">{$discount.description}</span></td>
					<td class="amount discountAmount">{$discount.formatted_amount}</td>
				</tr>
			{/foreach}
		{/if}

		<tr>
			<td colspan="3" class="subTotalCaption">
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