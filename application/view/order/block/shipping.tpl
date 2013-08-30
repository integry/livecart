{% if $cart.shippingSubtotal|@strlen %}
	<tr>
		<td colspan="{math equation="$extraColspanSize + 3"}" class="subTotalCaption">
			{% if !empty(isShippingEstimated) %}
				{t _estimated_shipping}:
			{% else %}
				{t _shipping}:
			{% endif %}
		</td>
		<td class="amount shippingAmount">[[cart.formatted_shippingSubtotal]]</td>
		[[ partial("order/block/cartUpdate.tpl") ]]
	</tr>
{% endif %}
