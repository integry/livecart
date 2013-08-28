[[ config('STORE_NAME') ]] Order Status Update
Kære {$user.fullName},

{if $order.shipments|@count == 1}
Vi har opdateret status for følgende ordre: <b class="orderID">#{$order.invoiceNumber}</b>.
{else}
Vi har opdateret status for een eller flere forsendelser for følgende ordre: <b class="orderID">#{$order.invoiceNumber}</b>.
{/if}

Hvis du har spørgsmål vedrørende denne ordre, er du velkommen til at kontakte os pr. E-mail, eller kontakte os på følgende side:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
Ny status: {if $shipment.status == 2}afventer forsendelse{elseif $shipment.status == 3}afstedt{elseif $shipment.status == 4}returneret{else}processing{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}