{foreach from=$shipment.items item="item" name="shipment"}
	<tr>
		{% if empty(hideSku) %}
		<td class="sku">
			[[item.Product.sku]]
		</td>
		{% endif %}

		<td class="productName">
			[[ partial("order/itemProductInfo.tpl") ]]
		</td>

		<td class="itemPrice {% if (string)$item.itemBasePrice > (string)$item.itemPrice %}discount{% endif %}">
			<div class="amount">[[item.formattedDisplaySubTotal]]</div>
			<div class="amountCalculcation">
				<small>[[item.count]]</span><span class="multiply"> x </span><span class="basePrice">[[item.formattedBasePrice]]</span><span class="actualPrice">[[item.formattedPrice]]</span></small>
			</div>

			{% if $item.recurringID %}
				{% if $recurringProductPeriodsByItemId[$item.ID] %}
					{assign var=period value=$recurringProductPeriodsByItemId[$item.ID]}
					({$period.ProductPrice_period.formated_price.$currency}
					{t _every}
					{% if $period.periodLength == 1 %}{t `$periodTypesSingle[$period.periodType]`}{% else %}[[period.periodLength]] {t `$periodTypesPlural[$period.periodType]`}{% endif %}{math equation="a * b" a=$period.periodLength|default:0 b=$period.rebillCount|default:0 assign="x"}{% if $x > 0 %} {t _for}  [[x]] {t `$periodTypesPlural[$period.periodType]`}, [[period.rebillCount]] {t _rebill_times}{% endif %})
				{% endif %}
			{% endif %}

		</td>
	</tr>
{/foreach}

{assign var="colspan" value=2-$hideSku}

{% if $shipment.taxes && !$hideTaxes && (!'HIDE_TAXES'|config || $showTaxes) %}
	<tr>
		<td colspan="[[colspan]]" class="subTotalCaption beforeTax">{t _subtotal_before_tax}:</td>
		<td class="amount">[[shipment.formatted_amount]]</td>
	</tr>
{% endif %}

{% if $order.isShippingRequired && $shipment.isShippable && $shipment.selectedRate %}
	<tr class="overviewShippingInfo">
		<td colspan="[[colspan]]" class="subTotalCaption">
			{t _shipping}{% if $shipment.ShippingService.name_lang %} ([[shipment.ShippingService.name_lang]]){% endif %}:
		</td>
		<td>
			{$shipment.selectedRate.taxPrice[$order.Currency.ID]|default:$shipment.selectedRate.formattedPrice[$order.Currency.ID]}
		</td>
	</tr>
{% endif %}
