[[ config('STORE_NAME') ]] Order Status Update
Kære [[user.fullName]],

{% if $order.shipments|@count == 1 %}
Vi har opdateret status for følgende ordre: <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
Vi har opdateret status for een eller flere forsendelser for følgende ordre: <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Hvis du har spørgsmål vedrørende denne ordre, er du velkommen til at kontakte os pr. E-mail, eller kontakte os på følgende side:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$order.shipments item=shipment}
Ny status: {% if $shipment.status == 2 %}afventer forsendelse{% elseif $shipment.status == 3 %}afstedt{% elseif $shipment.status == 4 %}returneret{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/en/signature.tpl") ]]