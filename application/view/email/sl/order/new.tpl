{'STORE_NAME'|config} Potrditev naročila
Spoštovani/a {$user.fullName},

Zahvaljujemo se vam za vaše naročilo, katero ste oddali na {'STORE_NAME'|config}. Če nas želite kontaktirati v zvezi tega naročila, prosimo vključite številko naročila <b class="orderID">#{$order.invoiceNumber}</b>.

Status vašega naročila lahko spremljate na tej strani:
{link controller=user action=viewOrder id=$order.ID url=true}

Če imate kakršna koli vprašanja glede vašega naročila, nam lahko pošljete sporočilo s klikom na zgornjo povezavo.

Naročili ste naslednje izdelke:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}