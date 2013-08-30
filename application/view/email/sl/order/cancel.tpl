[[ config('STORE_NAME') ]] Naročilo preklicano
Spoštovani/a [[user.fullName]],

Vaše naročilo <b class="orderID">#[[order.invoiceNumber]]</b>, naročeno na [[ config('STORE_NAME') ]], je bilo preklicano.

Če imate karkšna koli vprašanja glede naročila, nam lahko pošljete email ali nas kontaktirate preko naslednje strani:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Izdelki s preklicanega naročila:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]