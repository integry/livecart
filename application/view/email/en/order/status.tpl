[[ config('STORE_NAME') ]] Order Status Update
Dear [[user.fullName]],

{% if order.shipments|@count == 1 %}
Status has been updated for your order <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
Status has been updated for one or more shipments from your order <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

If you have any questions regarding this order, you can send us an email message or contact from the following page:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=order.shipments item=shipment}
New status: {% if shipment.status == 2 %}awaiting shipment{% elseif shipment.status == 3 %}shipped{% elseif shipment.status == 4 %}returned{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{% endfor %}

[[ partial("email/en/signature.tpl") ]]