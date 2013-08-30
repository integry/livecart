{assign var="colspan" value=2-$hideSku}

{% if empty(nochanges) %}
	<div class="orderOverviewControls">
		<a href="{link controller=order}">{t _any_changes}</a>
	</div>
{% endif %}

<table class="table table-striped table-condensed shipment{% if $order.isMultiAddress %} multiAddress{% endif %}" id="payItems">
	<thead>
		<tr>
			{% if empty(hideSku) %}<th class="sku">{t _sku}</th>{% endif %}
			<th class="productName">{t _product}</th>
			<th>{t _subtotal}</th>
		</tr>
	</thead>
	<tbody>

	{foreach from=$order.shipments key="key" item="shipment"}
		{% if $order.isMultiAddress %}
			<tr>
				<td colspan="{$colspan+1}" class="shipmentAddress">
					[[shipment.ShippingAddress.compact]]
				</td>
			</tr>
		{% endif %}
		[[ partial('order/compactOrderTableDetails.tpl', ['hideTaxes': true]) ]]
	{/foreach}

	{foreach from=$order.discounts item=discount}
		<tr>
			<td colspan="[[colspan]]" class="subTotalCaption"><span class="discountLabel">{% if $discount.amount > 0 %}{t _discount}{% else %}{t _surcharge}{% endif %}:</span> <span class="discountDesc">[[discount.description]]</span></td>
			<td class="amount discountAmount">[[discount.formatted_amount]]</td>
		</tr>
	{/foreach}

  	{% if !'HIDE_TAXES'|config %}
		{% if $order.taxes %}
			<tr>
				<td colspan="[[colspan]]" class="tax">{t _total_before_tax}:</td>
				<td>{$order.formattedTotalBeforeTax.$currency}</td>
			</tr>
		{% endif %}

		{foreach from=$order.taxes.$currency item="tax"}
			<tr>
				<td colspan="[[colspan]]" class="tax">[[tax.name_lang]]:</td>
				<td>[[tax.formattedAmount]]</td>
			</tr>
		{/foreach}
	{% endif %}

	<tr>
		<td colspan="[[colspan]]" class="subTotalCaption">{t _total}:</td>
		<td class="subTotal">{$order.formattedTotal.$currency}</td>
	</tr>

	</tbody>
</table>

[[ partial("order/fieldValues.tpl") ]]