[[ config('STORE_NAME') ]] Order statusuppdatering
Kära [[user.fullName]],

{% if order.shipments|@count == 1 %}
Status har uppdaterats fördin order <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
Status har uppdaterats för en eller flera leveranser av din <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Om du har frågor rörande din order kan du kontakta oss via följande länk:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=order.shipments item=shipment}
Ny status: {% if shipment.status == 2 %}avvaktar leverans {% elseif shipment.status == 3 %}levererad {% elseif shipment.status == 4 %}returnerad {% else %}under behandling{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{% endfor %}

[[ partial("email/en/signature.tpl") ]]