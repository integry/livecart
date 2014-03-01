{% if $order.isMultiAddress %}
	<div class="shipmentAddress">
		<span class="shipmentAddressLabel">{t _shipment_shipped_to}:</span> [[shipment.ShippingAddress.compact]]
	</div>
{% endif %}

<table class="table shipment">

	<thead>
		<tr>
			<th class="sku">{t _sku}</th>
			<th class="productName">{t _product}</th>
			<th>{t _price}</th>
			<th>{t _quantity}</th>
			<th>{t _subtotal}</th>
		</tr>
	</thead>

	<tbody>

		[[ partial("order/orderTableDetails.tpl") ]]

		{% if !'HIDE_TAXES'|config || $showTaxes %}
			{foreach from=$shipment.taxes item="tax"}
				<tr>
					<td colspan="4" class="tax">[[tax.TaxRate.Tax.name()]]:</td>
					<td>{$tax.formattedAmount[$order.Currency.ID]}</td>
				</tr>
			{/foreach}
		{% endif %}

		{% if $smarty.foreach.shipments.iteration == 1 %}
			{foreach from=$order.discounts item=discount}
				{% if $discount.amount != 0 %}
					<tr>
						<td colspan="4" class="subTotalCaption"><span class="discountLabel">{% if $discount.amount > 0 %}{t _discount}{% else %}{t _surcharge}{% endif %}:</span> <span class="discountDesc">[[discount.description]]</span></td>
						<td class="amount discountAmount">[[discount.formatted_amount]]</td>
					</tr>
				{% endif %}
			{/foreach}
		{% endif %}

		<tr>
			<td colspan="4" class="subTotalCaption">
				{% if $smarty.foreach.shipments.total > 1 %}
					{t _shipment_total}:
				{% else %}
					{t _order_total}:
				{% endif %}
			</td>
			<td class="subTotal">
				{% if $smarty.foreach.shipments.total == 1 %}
					{$order.formattedTotal[$order.Currency.ID]}
				{% else %}
					[[shipment.formatted_totalAmount]]
				{% endif %}
			</td>
		</tr>

	</tbody>

</table>