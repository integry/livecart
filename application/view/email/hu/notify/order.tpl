Új rendelés a [[ config('STORE_NAME') ]]
Rendelés ID: {$order.invoiceNumber}

Redenlés adminisztrlásáa:
{backendOrderUrl order=$order url=true}

A következő termékek lettek megrendelve:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}