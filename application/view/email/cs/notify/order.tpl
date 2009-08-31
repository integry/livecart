Přijata nová objednávka na {'STORE_NAME'|config}
Objednávka č.: {$order.invoiceNumber}

Manažer objednávky:
{backendOrderUrl order=$order url=true}

Byly objednány následující položky:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}