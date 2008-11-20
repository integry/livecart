<table class="table shipment">
	<thead>
		<tr>
			<th class="productName">{t _product}</th>
			<th class="shipmentPrice">{t _price}</th>
			<th class="shipmentQuantity">{t _quantity}</th>
			<th class="shipmentSubtotal">{t _subtotal}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$shipment.items item="item" name="shipment"}
			<tr class="{zebra loop="shipment"}">
				<td class="productName">
					<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
					{if $item.Product.variations}
						<span class="variations">
							({include file="order/itemVariationsList.tpl"})
						</span>
					{/if}
				</td>
				<td class="shipmentPrice">{$item.formattedDisplayPrice}</td>
				<td class="shipmentQuantity">{$item.count}</td>
				<td class="shipmentSubtotal">{$item.formattedDisplaySubTotal}</td>
			</tr>
		{/foreach}

		{if $shipment.taxes}
			<tr>
				<td colspan="3" class="subTotalCaption beforeTax">{t _subtotal_before_tax}:</td>
				<td>{$shipment.formattedSubTotalBeforeTax.$currency}</td>
			</tr>
		{/if}

		{foreach from=$shipment.taxes item="tax"}
			{if $tax.amount}
				<tr>
					<td colspan="3" class="tax">{$tax.TaxRate.Tax.name_lang} ({$tax.TaxRate.rate}%):</td>
					<td>{$tax.formattedAmount.$currency}</td>
				</tr>
			{/if}
		{/foreach}

		<tr>
			<td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
			<td class="subTotal">{$shipment.formattedSubTotal.$currency}</td>
		</tr>
	</tbody>
</table>
