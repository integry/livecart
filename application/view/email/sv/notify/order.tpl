Ny order hos  {'STORE_NAME'|config}
Order ID: {$order.ID}

Orderadministration:
{backendOrderUrl order=$order url=true}

Följande produkter har beställts:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/sv/signature.tpl"}