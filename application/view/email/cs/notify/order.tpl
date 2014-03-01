Přijata nová objednávka na [[ config('STORE_NAME') ]]
Objednávka č.: [[order.invoiceNumber]]

Manažer objednávky:
{backendOrderUrl order=order url=true}

Byly objednány následující položky:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]