{'STORE_NAME'|config} ได้ทำการยกเลิกออเดอร์ของคุณแล้ว
เรียนคุณ {$user.fullName},

ใบสั่งซื้อเลขที่ <b class="orderID">#{$order.ID}</b>ของท่าน ที่ได้สั่งซื้อกับ {'STORE_NAME'|config}ได้ถูกยกเลิกแล้ว

หากต้องการสอบถามหรือติดต่อกับทางร้าน คุณสามารถส่งอีเมล์มาได้หรือติดต่อผ่านแบบฟอร์มหน้าร้านตามลิ้งค์ด้านล่าง:
{link controller=user action=viewOrder id=$order.ID url=true}

สินค้าที่ถูกยกเลิก:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}