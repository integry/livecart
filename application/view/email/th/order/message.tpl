มีข้อความใหม่เกี่ยวกับออเดอร์ของคุณจาก {'STORE_NAME'|config}
เรียนคุณ {$user.fullName},

มีข้อความใหม่เกี่ยวกับออเดอร์ของคุณ

--------------------------------------------------
{$message.text}
--------------------------------------------------

คลิกลิ้งค์ด้านล่างนี้เท่านั้น เพื่อทำการตอบกลับข้อความนี้:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}