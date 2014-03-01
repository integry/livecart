[[ config('STORE_NAME') ]] Rendelés státusz frissítve
Kedves [[user.fullName]],

{% if order.shipments|@count == 1 %}
Rendelésed <b class="orderID">#[[order.invoiceNumber]]</b> státusza megváltozott.
{% else %}
Rendelésed <b class="orderID">#[[order.invoiceNumber]]</b>státusza egy vagy több szállítmányt illetően megváltozott.
{% endif %}

Ha bármilyen kérdésed van a rendeléssel kapcsolatosan, azt felteheted az alábbi oldalon:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=order.shipments item=shipment}
Új státusz: {% if shipment.status == 2 %}szállításra vár{% elseif shipment.status == 3 %}elküldve{% elseif shipment.status == 4 %}visszaérkezett{% else %}feldolgozás alatt{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{% endfor %}

[[ partial("email/en/signature.tpl") ]]