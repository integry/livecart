New Order Placed at [[ config('STORE_NAME') ]]
Užsakymo ID: {$order.invoiceNumber}

Užsakymo adresas:
{backendOrderUrl order=$order url=true}

Užsisakytas šias prekes:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/lt/signature.tpl"}