{'STORE_NAME'|config} pasūtījums atcelts
Cien. {$user.fullName},

Jūsu {'STORE_NAME'|config} pasūtījums #{$order.ID}, ir atcelts.

Ja Jums radušies kādi jautājumi sakarā ar šo pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet šo kontaktu formu:
{link controller=user action=viewOrder id=$order.ID url=true}

Produkti atceltajā pasūtījumā:
------------------------------------------------------------
Produkts                       Cena      Skaits    Summa
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------

{include file="email/lv/signature.tpl"}