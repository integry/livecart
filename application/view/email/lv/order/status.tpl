{'STORE_NAME'|config} Order Status Update
Cien. {$user.fullName},

{if $order.shipments|@count == 1}
Atjaunots pasūtījuma #{$order.ID} statuss.
{else}
Atjaunots viena vai vairāku sūtījumu statuss pasūtījumam #{$order.ID}.
{/if}

Ja radušies kādi jautājumi par pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet kontaktu formu šajā lapā:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Jaunais statuss: {if $shipment.status == 2}gaida sūtījumu{elseif $shipment.status == 3}nosūtīts{elseif $shipment.status == 4}atgriezts{else}tiek apstrādāts{/if}

------------------------------------------------------------
Produkts                       Cena      Skaits    Summa
------------------------------------------------------------
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
------------------------------------------------------------

{/foreach}

{include file="email/lv/signature.tpl"}