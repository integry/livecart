[[ config('STORE_NAME') ]] Ordre bekreftelse
Kjære {$user.fullName},

Takk for din bestilling fra [[ config('STORE_NAME') ]]. Hvis du har behov for å kontakte oss ang. denne bestillingen, vennligst oppgi følgende bestillingsnummer <b class="orderID">#{$order.invoiceNumber}</b>.

Du kan følge din bestilling på denne siden:
{link controller=user action=viewOrder id=$order.ID url=true}

Om du har spørmaål ang. denne bestillingen, kan du også sende en beskjed på linken over.

Vi minner om at følgende produkter er bestilt:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/no/signature.tpl"}