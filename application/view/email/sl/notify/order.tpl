Novo naročilo na [[ config('STORE_NAME') ]]
ID Naročila: [[order.invoiceNumber]]

Administracija Naročila:
{backendOrderUrl order=order url=true}

Naročeni so bili naslednji izdelki:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]