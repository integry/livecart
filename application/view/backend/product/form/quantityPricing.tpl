<div class="quantityPricing" style="display: none;">
	<table>
		<thead>
			<tr class="quantityRow"><td>
					<div class="quantityLabel">{tip _quantity} ▸</div>
					<div class="groupLabel">▾ {tip _group}</div>
				</td>
				<td>{textfield class="text quantity number" name="qQuantity[{$currency}][]"}</td></tr>
		</thead>
		<tbody>
			<tr><td class="groupColumn">{selectfield name="qGroup[{$currency}][]" options=$userGroups}</td><td>{textfield name="qPrice[{$currency}][]" class="text qprice number"}</td></tr>
		</tbody>
	</table>
</div>