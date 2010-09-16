<div class="quantityPricing" style="display: none;" id="quantityPricingViewPort_{$product.ID}">
<table id="quantityPricing_{$currency}_{$product.ID}">
	<thead>
		<tr class="quantityRow"><td>
				<div class="quantityLabel">{t _quantity} ▸</div>
				<div class="groupLabel">▾ {t _group}</div>
			</td>
			<td><input type="text" class="text quantity number" name="qQuantity[{$currency}][]" /></td></tr>
	</thead>
	<tbody>
		<tr><td class="groupColumn">{selectfield name="qGroup[{$currency}][]" options=$userGroups}</td><td><input type="text" name="qPrice[{$currency}][]" class="text qprice number" /></td></tr>
	</tbody>
</table>
<input type="hidden" class="hiddenValue" name="quantityPricing[{$currency}]" />
</div>

<script type="text/javascript">
	new Backend.Product.QuantityPrice($('quantityPricing_{$currency}_{$product.ID}'), {json array=$prices[$currency]});
</script>
