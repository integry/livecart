Ny order hos  [[ config('STORE_NAME') ]]
Order ID: [[order.invoiceNumber]]

Orderadministration:
{backendOrderUrl order=order url=true}

Följande produkter har beställts:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/sv/signature.tpl") ]]