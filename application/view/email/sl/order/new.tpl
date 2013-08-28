[[ config('STORE_NAME') ]] Potrditev naročila
Spoštovani/a [[user.fullName]],

Zahvaljujemo se vam za vaše naročilo, katero ste oddali na [[ config('STORE_NAME') ]]. Če nas želite kontaktirati v zvezi tega naročila, prosimo vključite številko naročila <b class="orderID">#[[order.invoiceNumber]]</b>.

Status vašega naročila lahko spremljate na tej strani:
{link controller=user action=viewOrder id=$order.ID url=true}

Če imate kakršna koli vprašanja glede vašega naročila, nam lahko pošljete sporočilo s klikom na zgornjo povezavo.

Naročili ste naslednje izdelke:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]