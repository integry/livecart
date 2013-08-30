[[ config('STORE_NAME') ]] Ordre bekreftelse
Kjære [[user.fullName]],

Takk for din bestilling fra [[ config('STORE_NAME') ]]. Hvis du har behov for å kontakte oss ang. denne bestillingen, vennligst oppgi følgende bestillingsnummer <b class="orderID">#[[order.invoiceNumber]]</b>.

Du kan følge din bestilling på denne siden:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Om du har spørmaål ang. denne bestillingen, kan du også sende en beskjed på linken over.

Vi minner om at følgende produkter er bestilt:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/no/signature.tpl") ]]