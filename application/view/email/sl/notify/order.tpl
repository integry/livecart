Novo naročilo na [[ config('STORE_NAME') ]]
ID Naročila: [[order.invoiceNumber]]

Administracija Naročila:
{backendOrderUrl order=$order url=true}

Naročeni so bili naslednji izdelki:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}