Jaunumi par [[ config('STORE_NAME') ]] pasūtījumu
Cien. [[user.fullName]],

{% if order.shipments|@count == 1 %}
Atjaunots pasūtījuma <b class="orderID">#[[order.invoiceNumber]]</b> statuss.
{% else %}
Atjaunots viena vai vairāku sūtījumu statuss pasūtījumam <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

Ja radušies kādi jautājumi par pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet kontaktu formu šajā lapā:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{% for shipment in shipments %}
Jaunais statuss: {% if shipment.status == 2 %}gaida sūtījumu{% elseif shipment.status == 3 %}nosūtīts{% elseif shipment.status == 4 %}atgriezts{% else %}tiek apstrādāts{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{% endfor %}

[[ partial("email/lv/signature.tpl") ]]