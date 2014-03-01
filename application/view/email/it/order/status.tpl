[[ config('STORE_NAME') ]] Stato Ordine Aggiornato
Gentile [[user.fullName]],

{% if order.shipments|@count == 1 %}
Abbiamo aggiornato lo stato del tuo ordine <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
Abbiamo aggiornato lo stato di una o più spedizioni di prodotti riguardanti il tuo ordine <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Se avessi domande in merito a questo ordine, puoi inviarci una email o contattarci direttamente
da questa pagina:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=order.shipments item=shipment}
Nuovo stato: {% if shipment.status == 2 %}in attesa di spedizione{% elseif shipment.status == 3 %}spedito{% elseif shipment.status == 4 %}rientrato{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{% endfor %}

[[ partial("email/it/signature.tpl") ]]