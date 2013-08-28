[[ config('STORE_NAME') ]] pasūtījums atcelts
Cien. {$user.fullName},

Jūsu [[ config('STORE_NAME') ]] pasūtījums <b class="orderID">#{$order.invoiceNumber}</b>, ir atcelts.

Ja Jums radušies kādi jautājumi sakarā ar šo pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet šo kontaktu formu:
{link controller=user action=viewOrder id=$order.ID url=true}

Produkti atceltajā pasūtījumā:
{include file="email/blockOrderItems.tpl"}

{include file="email/lv/signature.tpl"}