[[ config('STORE_NAME') ]] ยืนยันการสั่งซื้อ
เรียนคุณ [[user.fullName]],

ขอบคุณที่สั่งซื้อสินค้ากับ [[ config('STORE_NAME') ]]หากคุณต้องการติดต่อสอบถามเพิ่มเติมเกี่ยวกับออเดอร์นี้ กรุณาระบุรหัสใบสั่งซื้อในการติดต่อ ซึ่งรหัสใบสั่งซื้อของคุณคือ <b class="orderID">#[[order.invoiceNumber]]</b>

คุณสามารถติดตามสถานะการจัดการสินค้าของคุณได้ที่ลิ้งค์ด้านล่าง:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

ซึ่งหากคุณต้องการติดต่อสอบถามเพิ่มเติมก็สามารถติดต่อเราได้ที่ลิ้งค์ด้านบนเช่นกัน

ด้านล่างนี้คือสินค้าที่คุณสั่งซื้อเข้ามา:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]