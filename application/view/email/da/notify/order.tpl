New Order Placed at {'STORE_NAME'|config}
Ordre ID: {$order.invoiceNumber}

Ordreadministration:
{backendOrderUrl order=$order url=true}

Følgende enheder er blevet bestilt:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}