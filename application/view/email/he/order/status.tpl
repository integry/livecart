[[ config('STORE_NAME') ]] עדכון מצב הזמנה
לכבוד [[user.fullName]],

{% if $order.shipments|@count == 1 %}
עדכון מצב בנוגע להזמנה שלך <b class="orderID">#[[order.invoiceNumber]]</b>.
{% else %}
המצב עודכן עבור משלוח אחד או יותר מההזמנה שלך <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

אם יש לך שאלות כלשהם הנוגעות להזמנה זו, אנא אל תהסס לשלוח אלינו אימייל או ליצור עימנו קשר באמצעות הקישור הבא::
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$order.shipments item=shipment}
מצב חדש: {% if $shipment.status == 2 %}ממתין למשלוח{% elseif $shipment.status == 3 %}shipped{% elseif $shipment.status == 4 %}returned{% else %}בתהליך{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/en/signature.tpl") ]]