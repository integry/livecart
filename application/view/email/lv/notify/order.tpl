Saņemts jauns pasūtījums [[ config('STORE_NAME') ]]
Pasūtījuma ID: [[order.invoiceNumber]]

Pasūtījuma administrācija:
{backendOrderUrl order=$order url=true}

Pasūtītas sekojošas preces:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/lv/signature.tpl") ]]