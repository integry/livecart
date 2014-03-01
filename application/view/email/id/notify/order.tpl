Order Baru Dibuat di [[ config('STORE_NAME') ]]
No. Order: [[order.invoiceNumber]]

Administrasi order:
{backendOrderUrl order=order url=true}

Barang-barang berikut telah dipesan:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/id/signature.tpl") ]]