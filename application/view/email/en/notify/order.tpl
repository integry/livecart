New Order Placed at [[ config('STORE_NAME') ]]
Order ID: [[order.invoiceNumber]]

Order administration:
{backendOrderUrl order=$order url=true}

The following items have been ordered:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]