[[ config('STORE_NAME') ]] Bestilling kansellert
Kjære [[user.fullName]],

Din bestillingsnr. [[order.invoiceNumber]], fra [[ config('STORE_NAME') ]], er kansellert.

Hvis du har noen spørmål rundt dette, kan du sende en email eller kontakte oss på denne linken:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Produkter som er kansellert:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/no/signature.tpl") ]]