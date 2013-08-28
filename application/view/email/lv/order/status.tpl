Jaunumi par [[ config('STORE_NAME') ]] pasūtījumu
Cien. {$user.fullName},

{if $order.shipments|@count == 1}
Atjaunots pasūtījuma <b class="orderID">#{$order.invoiceNumber}</b> statuss.
{else}
Atjaunots viena vai vairāku sūtījumu statuss pasūtījumam <b class="orderID">#{$order.invoiceNumber}</b>.
{/if}

Ja radušies kādi jautājumi par pasūtījumu, lūdzu sūtiet e-pastu vai izmantojiet kontaktu formu šajā lapā:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Jaunais statuss: {if $shipment.status == 2}gaida sūtījumu{elseif $shipment.status == 3}nosūtīts{elseif $shipment.status == 4}atgriezts{else}tiek apstrādāts{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/lv/signature.tpl"}