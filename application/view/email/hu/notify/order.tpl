Új rendelés a [[ config('STORE_NAME') ]]
Rendelés ID: [[order.invoiceNumber]]

Redenlés adminisztrlásáa:
{backendOrderUrl order=$order url=true}

A következő termékek lettek megrendelve:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]