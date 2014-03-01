{% if empty(html) %}
[[ partial("email/blockItemHeader.tpl") ]]
{foreach from=order.shipments item=shipment}
[[ partial("email/blockShipment.tpl") ]]
{% endfor %}
------------------------------------------------------------{% if config('SHOW_SKU_EMAIL') %}----------{% endif %}
{% endif %}{*html*}
{% if !empty(html) %}
{% if empty(noTable) %}<table>{% endif %}
[[ partial('email/blockItemHeader.tpl', ['noTable': true]) ]]
{foreach from=order.shipments item=shipment}
[[ partial('email/blockShipment.tpl', ['noTable': true]) ]]
{% endfor %}
{% if empty(noTable) %}</table>{% endif %}
{% endif %}{*html*}