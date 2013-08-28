New Order Placed at [[ config('STORE_NAME') ]]
Ordre ID: [[order.invoiceNumber]]

Ordreadministration:
{backendOrderUrl order=$order url=true}

Følgende enheder er blevet bestilt:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]