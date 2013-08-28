New Order Placed at [[ config('STORE_NAME') ]]
Order ID: [[order.invoiceNumber]]

Order administration:
{backendOrderUrl order=$order url=true}

The following items have been ordered:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}