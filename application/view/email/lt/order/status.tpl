[[ config('STORE_NAME') ]] Order Status Update
Gerbiama(-s) [[user.fullName]],

{% if $order.shipments|@count == 1 %}
Pasikeitė Jūsų užsakymo <b class="orderID">#[[order.invoiceNumber]]</b> būsena.
{% else %}
Būsena pakito vienam ar daugiau Jūsų užsakymo <b class="orderID">#[[order.invoiceNumber]]</b> siuntinių.
{% endif %}

Jei turite klausimų, susijusių su šiuo užsakymu, galite siųsti laišką ar susisiekti su mumis iš šio puslapio:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$shipments item=shipment}
Nauja būsena: {% if $shipment.status == 2 %}laukia siuntimo{% elseif $shipment.status == 3 %}išsiųstas{% elseif $shipment.status == 4 %}grąžintas{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/lt/signature.tpl") ]]