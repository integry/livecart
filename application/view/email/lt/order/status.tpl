{'STORE_NAME'|config} Order Status Update
Gerbiama(-s) {$user.fullName},

{if $order.shipments|@count == 1}
Pasikeitė Jūsų užsakymo #{$order.ID} būsena.
{else}
Būsena pakito vienam ar daugiau Jūsų užsakymo #{$order.ID} siuntinių.
{/if}

Jei turite klausimų, susijusių su šiuo užsakymu, galite siųsti laišką ar susisiekti su mumis iš šio puslapio:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
Nauja būsena: {if $shipment.status == 2}laukia siuntimo{elseif $shipment.status == 3}išsiųstas{elseif $shipment.status == 4}grąžintas{else}processing{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/lt/signature.tpl"}