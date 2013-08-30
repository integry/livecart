{% if !$html %}
[[ partial("email/blockItemHeader.tpl") ]]
{foreach from=$order.shipments item=shipment}
[[ partial("email/blockShipment.tpl") ]]
{/foreach}
------------------------------------------------------------{% if 'SHOW_SKU_EMAIL'|config %}----------{% endif %}
{% endif %}{*html*}
{% if $html %}
{% if !$noTable %}<table>{% endif %}
{include file="email/blockItemHeader.tpl" noTable=true}
{foreach from=$order.shipments item=shipment}
{include file="email/blockShipment.tpl" noTable=true}
{/foreach}
{% if !$noTable %}</table>{% endif %}
{% endif %}{*html*}