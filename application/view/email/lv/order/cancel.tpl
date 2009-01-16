{'STORE_NAME'|config} pasūtījums atcelts
Cien. {$user.fullName},

Jūsu {'STORE_NAME'|config} pasūtījums <b class="orderID">#{$order.ID}</b>, ir atcelts.

Ja Jums radušies kādi jautājumi sakarā ar šo pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet šo kontaktu formu:
{link controller=user action=viewOrder id=$order.ID url=true}

Produkti atceltajā pasūtījumā:
{include file="email/blockOrderItems.tpl"}

{include file="email/lv/signature.tpl"}