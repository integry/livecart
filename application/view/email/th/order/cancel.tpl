[[ config('STORE_NAME') ]] ได้ทำการยกเลิกออเดอร์ของคุณแล้ว
เรียนคุณ [[user.fullName]],

ใบสั่งซื้อเลขที่ <b class="orderID">#[[order.invoiceNumber]]</b>ของท่าน ที่ได้สั่งซื้อกับ [[ config('STORE_NAME') ]]ได้ถูกยกเลิกแล้ว

หากต้องการสอบถามหรือติดต่อกับทางร้าน คุณสามารถส่งอีเมล์มาได้หรือติดต่อผ่านแบบฟอร์มหน้าร้านตามลิ้งค์ด้านล่าง:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

สินค้าที่ถูกยกเลิก:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]