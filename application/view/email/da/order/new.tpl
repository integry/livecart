[[ config('STORE_NAME') ]] Order Confirmation
Kære [[user.fullName]],

Tak for din ordre, som du netop har afgivet på [[ config('STORE_NAME') ]]. Ved henvendelse vedrørende denne ordre, oplyst da venligst følgende ordre ID <b class="orderID">#[[order.invoiceNumber]]</b>.

Du vil kunne følge din ordrestatus på denne side:
{link controller=user action=viewOrder id=$order.ID url=true}

Hvis du har spørgsmål vedrørende denne ordre, kan dette ligeledes gøres på ovenstående side.

Vi påminder om, at følgende enheder er blevet bestilt:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}