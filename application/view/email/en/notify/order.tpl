New Order Placed at {'STORE_NAME'|config}
Order ID: {$order.ID}

Order administration:
{backendOrderUrl order=$order url=true}

The following items have been ordered:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}