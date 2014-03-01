{% if config('SHOW_SKU_EMAIL') %}{% set SHOW_SKU = true %}{% endif %}{% if empty(html) %}
[[ partial("email/blockOrderItems.tpl") ]]

{% if order.taxes[order.Currency.ID] && !config('HIDE_TAXES') %}
[[ @str_pad_left:49({t _subtotal}) ]]: [[order.formatted_itemSubtotalWithoutTax]]
{% endif %}
{% if order.formatted_shippingSubtotal %}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}[[ @str_pad_left:49({t _shipping}) ]]: [[order.formatted_shippingSubtotal]]
{% endif %}
{% if order.taxes[order.Currency.ID] && !config('HIDE_TAXES') %}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}{''|@str_pad_left:27}---------------------------------
[[ @str_pad_left:49({t _subtotal_before_tax}) ]]: [[order.formatted_subtotalBeforeTaxes]]
{foreach from=order.taxes[order.Currency.ID] item=tax}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}{tax.name()|@str_pad_left:49}: [[tax.formattedAmount]]
{% endfor %}
{% endif %}
{foreach from=order.discounts item=discount}
{% if discount.amount != 0 %}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}{discount.description|@str_pad_left:49}: [[discount.formatted_amount]]{% endif %}
{% endfor %}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}{''|@str_pad_left:27}---------------------------------
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}[[ @str_pad_left:49({t _grand_total}) ]]: {order.formattedTotal[order.Currency.ID]}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}[[ @str_pad_left:49({t _amount_paid}) ]]: [[order.formatted_amountPaid]]
{% if order.amountDue %}
{% if !empty(SHOW_SKU) %}{''|@str_pad_left:10}{% endif %}[[ @str_pad_left:49({t _amount_due}) ]]: [[order.formatted_amountDue]]
{% endif %}
{% endif %}{*html*}
{% if !empty(html) %}
<table border="1">
[[ partial('email/blockOrderItems.tpl', ['noTable': true]) ]]

{% if order.taxes[order.Currency.ID] && !config('HIDE_TAXES') %}
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">{t _subtotal}</td><td align="right">[[order.formatted_itemSubtotalWithoutTax]]</td></tr>
{% endif %}
{% if order.formatted_shippingSubtotal %}
	{% if order.shipments|@count == 1 %}
		[[ partial('email/blockShippingCost.tpl', ['shipment': order.shipments.0]) ]]
	{% else %}
		<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">{t _shipping}</td><td align="right">[[order.formatted_shippingSubtotal]]</td></tr>
	{% endif %}
{% endif %}
{% if order.taxes[order.Currency.ID] && !config('HIDE_TAXES') %}
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">{t _subtotal_before_tax}</td><td align="right">[[order.formatted_subtotalBeforeTaxes]]</td></tr>
{foreach from=order.taxes[order.Currency.ID] item=tax}
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">[[tax.name()]]</td><td align="right">[[tax.formattedAmount]]</td></tr>
{% endfor %}
{% endif %}
{foreach from=order.discounts item=discount}
{% if discount.amount != 0 %}
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">[[discount.description]]</td><td align="right">[[discount.formatted_amount]]</td></tr>
{% endif %}
{% endfor %}
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">{t _grand_total}</td><td align="right"><b>{order.formattedTotal[order.Currency.ID]}</b></td></tr>
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">{t _amount_paid}</td><td align="right">[[order.formatted_amountPaid]]</td></tr>
{% if order.amountDue %}
<tr><td colspan="{% if !empty(SHOW_SKU) %}4{% else %}3{% endif %}">{t _amount_due}</td><td align="right">[[order.formatted_amountDue]]</td></tr>
{% endif %}
</table>
{% endif %}{*html*}