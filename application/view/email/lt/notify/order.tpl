New Order Placed at [[ config('STORE_NAME') ]]
Užsakymo ID: [[order.invoiceNumber]]

Užsakymo adresas:
{backendOrderUrl order=order url=true}

Užsisakytas šias prekes:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/lt/signature.tpl") ]]