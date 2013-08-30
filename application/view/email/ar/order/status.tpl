[[ config('STORE_NAME') ]] تحديث حالة الطلب
Dear [[user.fullName]],

{% if $order.shipments|@count == 1 %}
وقد تم تحديث الحلة لطلبك <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
وقد تم تحديث واحد أو أكثر من الشحنات طلبك<b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

إذا كان لديك أي أسئلة بخصوص هذا النظام ، ويمكنك أن ترسل لنا رسالة عبر البريد الإلكتروني أو الاتصال من الصفحة التالية :
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$order.shipments item=shipment}
حالة جديدة : {% if $shipment.status == 2 %}في انتظار شحنة{% elseif $shipment.status == 3 %}شحنت{% elseif $shipment.status == 4 %}عاد{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/en/signature.tpl") ]]