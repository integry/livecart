มีผู้สั่งซื้อสินค้าเข้ามาใหม่ที่ {'STORE_NAME'|config}
ใบสั่งซื้อเลขที่: {$order.ID}

การจัดการออเดอร์:
{backendOrderUrl order=$order url=true}

รายการสินค้าที่สั่งซื้อเข้ามา:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}