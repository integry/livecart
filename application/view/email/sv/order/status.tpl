{'STORE_NAME'|config} Order statusuppdatering
Kära {$user.fullName},

{if $order.shipments|@count == 1}
Status har uppdaterats fördin order #{$order.ID}.
{else}
Status har uppdaterats för en eller flera leveranser av din #{$order.ID}.
{/if}

Om du har frågor rörande din order kan du kontakta oss via följande länk:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
Ny status: {if $shipment.status == 2}avvaktar leverans {elseif $shipment.status == 3}levererad {elseif $shipment.status == 4}returnerad {else}under behandling{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}