มีการสั่งซื้อสินค้าใหม่จาก {'STORE_NAME'|config}
มีคนสั่งซื้อสินค้าเข้ามาใหม่ ใบสั่งซื้อเลขที่ #{$order.ID}

--------------------------------------------------
{$message.text}
--------------------------------------------------

คุณสามารถตอบรับออเดอร์นี้ได้ที่ระบบบริหารหลังร้าน:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}