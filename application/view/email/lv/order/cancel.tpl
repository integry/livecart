[[ config('STORE_NAME') ]] pasūtījums atcelts
Cien. [[user.fullName]],

Jūsu [[ config('STORE_NAME') ]] pasūtījums <b class="orderID">#[[order.invoiceNumber]]</b>, ir atcelts.

Ja Jums radušies kādi jautājumi sakarā ar šo pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet šo kontaktu formu:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Produkti atceltajā pasūtījumā:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/lv/signature.tpl") ]]