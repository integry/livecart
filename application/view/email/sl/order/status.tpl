{'STORE_NAME'|config} Obnovljen status naročila
Spoštovani/a {$user.fullName},

{if $order.shipments|@count == 1}
Status vašega naročila <b class="orderID">#{$order.invoiceNumber}</b> je bil obnovljen.
{else}
Status vašega naročila za eno ali več pošiljk je bil obnovljen <b class="orderID">#{$order.invoiceNumber}</b>.
{/if}

Če imate kakršna koli vprašanja glede tega naročila nam lahko pošljete email ali nas kontaktirate s naslednje strani:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
Novi status: {if $shipment.status == 2}awaiting shipment{elseif $shipment.status == 3}shipped{elseif $shipment.status == 4}returned{else}processing{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}