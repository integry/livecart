มีผู้สั่งซื้อสินค้าเข้ามาใหม่ที่ [[ config('STORE_NAME') ]]
ใบสั่งซื้อเลขที่: [[order.invoiceNumber]]

การจัดการออเดอร์:
{backendOrderUrl order=order url=true}

รายการสินค้าที่สั่งซื้อเข้ามา:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]