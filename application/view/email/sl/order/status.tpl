[[ config('STORE_NAME') ]] Obnovljen status naročila
Spoštovani/a [[user.fullName]],

{% if $order.shipments|@count == 1 %}
Status vašega naročila <b class="orderID">#[[order.invoiceNumber]]</b> je bil obnovljen.
{% else %}
Status vašega naročila za eno ali več pošiljk je bil obnovljen <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Če imate kakršna koli vprašanja glede tega naročila nam lahko pošljete email ali nas kontaktirate s naslednje strani:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$order.shipments item=shipment}
Novi status: {% if $shipment.status == 2 %}awaiting shipment{% elseif $shipment.status == 3 %}shipped{% elseif $shipment.status == 4 %}returned{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/en/signature.tpl") ]]