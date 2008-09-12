{'STORE_NAME'|config} Rendelés státusz frissítve
Kedves {$user.fullName},

{if $order.shipments|@count == 1}
Rendelésed #{$order.ID} státusza megváltozott.
{else}
Rendelésed #{$order.ID}státusza egy vagy több szállítmányt illetően megváltozott.
{/if}

Ha bármilyen kérdésed van a rendeléssel kapcsolatosan, azt felteheted az alábbi oldalon:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
Új státusz: {if $shipment.status == 2}szállításra vár{elseif $shipment.status == 3}elküldve{elseif $shipment.status == 4}visszaérkezett{else}feldolgozás alatt{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}