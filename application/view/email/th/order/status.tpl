[[ config('STORE_NAME') ]] อัพเดทสถานะออเดอร์เรียบร้อย
เรียนคุณ [[user.fullName]],

{% if $order.shipments|@count == 1 %}
มีการปรับเปลี่ยนสถานะของใบสั่งซื้อเลขที่ <b class="orderID">#[[order.invoiceNumber]]</b>ของคุณแล้ว
{% else %}
สถานะได้ถูกอัพเดทความคืบหน้าในการจัดการสินค้าของคุณจากใบสั่งซื้อเลขที่ <b class="orderID">#[[order.invoiceNumber]]</b>.
{% endif %}

หากคุณต้องการติดต่อสอบถามเพิ่มเติมเกี่ยวกับสินค้า คุณสามารถส่งอีเมล์หาเราได้โดยตรงหรือจากแบบฟอร์มในหน้านี้:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

{foreach from=$order.shipments item=shipment}
สถานะใหม่: {% if $shipment.status == 2 %}รอการจัดส่ง{% elseif $shipment.status == 3 %}ส่งสินค้าเรียบร้อยแล้ว{% elseif $shipment.status == 4 %}ส่งคืน{% else %}processing{% endif %}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/en/signature.tpl") ]]