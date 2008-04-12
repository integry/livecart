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
		{foreach from=$shipment.items item="item" name="shipment"}
			<tr class="{zebra loop="shipment"}">
				<td class="productName"><a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a></td>
				<td>{$item.formattedDisplayPrice}</td>
				<td>{$item.count}</td>
				<td>{$item.formattedDisplaySubTotal}</td>
			</tr>
		{/foreach}

		{if $shipment.taxes}
			<tr>
				<td colspan="3" class="subTotalCaption beforeTax">{t _subtotal_before_tax}:</td>
				<td>{$shipment.formattedSubTotalBeforeTax.$currency}</td>
			</tr>
		{/if}

		{foreach from=$shipment.taxes item="tax"}
			<tr>
				<td colspan="3" class="tax">{$tax.TaxRate.Tax.name_lang} ({$tax.TaxRate.rate}%):</td>
				<td>{$tax.formattedAmount.$currency}</td>
			</tr>
		{/foreach}

		<tr>
			<td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
			<td class="subTotal">{$shipment.formattedSubTotal.$currency}</td>
		</tr>
	</tbody>
</table>
