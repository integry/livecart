{% if empty(html) %}
[[ partial("email/blockItemHeader.tpl") ]]
{foreach from=$order.shipments item=shipment}
[[ partial("email/blockShipment.tpl") ]]
{/foreach}
------------------------------------------------------------{% if 'SHOW_SKU_EMAIL'|config %}----------{% endif %}
{% endif %}{*html*}
{% if !empty(html) %}
{% if empty(noTable) %}<table>{% endif %}
[[ partial('email/blockItemHeader.tpl', ['noTable': true]) ]]
{foreach from=$order.shipments item=shipment}
[[ partial('email/blockShipment.tpl', ['noTable': true]) ]]
{/foreach}
{% if empty(noTable) %}</table>{% endif %}
{% endif %}{*html*}