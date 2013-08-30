[[ config('STORE_NAME') ]] Oppdatering av status på bestilling
Kjære [[user.fullName]],

{% if $order.shipments|@count == 1 %}
Status er oppdatert for bestillingsnr.[[order.invoiceNumber]].
{% else %}
Status er oppdatert for en eller flere forsendelser av din bestillingsordre <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Hvis du har spørsmål ang. denne bestillingen, kan du sende oss en mail eller kontakte oss på følgene link:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$shipments item=shipment}
Ny status: {% if $shipment.status == 2 %}Forventet levering{% elseif $shipment.status == 3 %}sendt{% elseif $shipment.status == 4 %}returnert{% else %}under behandling {% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/no/signature.tpl") ]]