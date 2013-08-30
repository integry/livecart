{assign var="colspan" value=$colspan|@or:4}

{foreach from=$shipment.items item="item" name="shipment"}
	<tr class="{% if $productsInSeparateLine %} topLine{% endif %}">

		{% if !$hideSku %}
		<td class="sku">
			[[item.Product.sku]]
		</td>
		{% endif %}

		{% if !$productsInSeparateLine %}
			<td class="productName">
				[[ partial("order/itemProductInfo.tpl") ]]
			</td>
		{% endif %}

		<td class="itemPrice {% if (string)$item.itemBasePrice > (string)$item.itemPrice %}discount{% endif %}">
			<span class="basePrice">[[item.formattedBasePrice]]</span><span class="actualPrice">[[item.formattedPrice]]</span>

			{% if $item.recurringID %}
				{% if $recurringProductPeriodsByItemId[$item.ID] %}
					{assign var=period value=$recurringProductPeriodsByItemId[$item.ID]}
					({$period.ProductPrice_period.formated_price.$currency}
					{t _every}
					{% if $period.periodLength == 1 %}{t `$periodTypesSingle[$period.periodType]`}{% else %}[[period.periodLength]] {t `$periodTypesPlural[$period.periodType]`}{% endif %}{math equation="a * b" a=$period.periodLength|default:0 b=$period.rebillCount|default:0 assign="x"}{% if $x > 0 %} {t _for}  [[x]] {t `$periodTypesPlural[$period.periodType]`}, [[period.rebillCount]] {t _rebill_times}{% endif %})
				{% endif %}
			{% endif %}

		</td>
		<td class="itemCount">[[item.count]]</td>
		<td class="amount">[[item.formattedDisplaySubTotal]]</td>
	</tr>
	{% if $productsInSeparateLine %}
		<tr>
			<td class="productName productNameRow" colspan="{$colspan+1}">
				[[ partial("order/itemProductInfo.tpl") ]]
			</td>
		</tr>
	{% endif %}
{/foreach}

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
