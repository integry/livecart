Order Baru Dibuat di [[ config('STORE_NAME') ]]
No. Order: {$order.invoiceNumber}

Administrasi order:
{backendOrderUrl order=$order url=true}

Barang-barang berikut telah dipesan:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/id/signature.tpl"}