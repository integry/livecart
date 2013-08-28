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
			<tr>
				<td class="productName">
					{if $item.Product.ID}
						<a href="{productUrl product=$item.Product}">[[item.Product.name_lang]]</a>
					{else}
						<span>[[item.Product.name_lang]]</span>
					{/if}

					{if $item.recurringID && $recurringPlans[$item.recurringID]}
						{assign var="period" value=$recurringPlans[$item.recurringID]}
						{$period.name_lang|escape}

						<span class="recurringPlan">
						({$period.ProductPrice_period.formated_price.$currency}
							{t _every}
							{if $period.periodLength == 1}
								{t `$periodTypesSingle[$period.periodType]`}
							{else}
								[[period.periodLength]] {t `$periodTypesPlural[$period.periodType]`}
							{/if}
							{math equation="a * b" a=$period.periodLength|default:0 b=$period.rebillCount|default:0 assign="x"}
							{if $x > 0}
								{t _for}
								[[x]]
								{t `$periodTypesPlural[$period.periodType]`}, [[period.rebillCount]] {t _rebill_times}{*
									{if $period.ProductPrice_setup}
										{t _setup_fee} {$period.ProductPrice_period.formated_price.$currency}
									{/if}
								*}{/if})
						</span>
					{/if}

					{if $item.Product.variations}
						<span class="variations">
							([[ partial("order/itemVariationsList.tpl") ]])
						</span>
					{/if}
				</td>
				<td class="shipmentPrice">[[item.formattedDisplayPrice]]</td>
				<td class="shipmentQuantity">[[item.count]]</td>
				<td class="shipmentSubtotal">[[item.formattedDisplaySubTotal]]</td>
			</tr>
		{/foreach}

		{if !'HIDE_TAXES'|config}
			{if $shipment.taxes}
				<tr>
					<td colspan="3" class="subTotalCaption beforeTax">{t _subtotal_before_tax}:</td>
					<td>{$shipment.formattedSubTotalBeforeTax.$currency}</td>
				</tr>
			{/if}

			{foreach from=$shipment.taxes item="tax"}
				{if $tax.amount}
					<tr>
						<td colspan="3" class="tax">[[tax.TaxRate.Tax.name_lang]] ([[tax.TaxRate.rate]]%):</td>
						<td>{$tax.formattedAmount.$currency}</td>
					</tr>
				{/if}
			{/foreach}
		{/if}

		<tr>
			<td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
			<td class="subTotal">{$shipment.formattedSubTotal.$currency}</td>
		</tr>
	</tbody>
</table>
