{foreach from=cart.discounts item=discount}
	{% if discount.amount != 0 %}
		<tr>
			<td colspan="{math equation="extraColspanSize + 3"}" class="subTotalCaption"><span class="discountLabel">{% if discount.amount > 0 %}{t _discount}{% else %}{t _surcharge}{% endif %}:</span> <span class="discountDesc">[[discount.description]]</span></td>
			<td class="amount discountAmount">[[discount.formatted_amount]]</td>
			[[ partial("order/block/cartUpdate.tpl") ]]
		</tr>
	{% endif %}
{% endfor %}

{% if cart.itemDiscountReverse %}
	{% if discount.amount != 0 %}
		<tr>
			<td colspan="{math equation="extraColspanSize + 3"}" class="subTotalCaption"><span class="discountLabel">{% if cart.itemDiscountReverse < 0 %}{t _discount}{% else %}{t _surcharge}{% endif %}:</span></td>
			<td class="amount discountAmount">[[cart.formatted_itemDiscountReverse]]</td>
			[[ partial("order/block/cartUpdate.tpl") ]]
		</tr>
	{% endif %}
{% endif %}
