Ny order hos  [[ config('STORE_NAME') ]]
Order ID: {$order.invoiceNumber}

Orderadministration:
{backendOrderUrl order=$order url=true}

Följande produkter har beställts:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/sv/signature.tpl"}