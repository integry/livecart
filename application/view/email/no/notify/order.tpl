Ny bestilling hos [[ config('STORE_NAME') ]]
Bestillingsnr: [[order.invoiceNumber]]

Order administration:
{backendOrderUrl order=order url=true}

Følgende produkter er bestilt:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/no/signature.tpl") ]]