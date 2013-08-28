Přijata nová objednávka na [[ config('STORE_NAME') ]]
Objednávka č.: [[order.invoiceNumber]]

Manažer objednávky:
{backendOrderUrl order=$order url=true}

Byly objednány následující položky:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}