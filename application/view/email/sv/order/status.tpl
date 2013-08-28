[[ config('STORE_NAME') ]] Order statusuppdatering
Kära [[user.fullName]],

{if $order.shipments|@count == 1}
Status har uppdaterats fördin order <b class="orderID">#[[order.invoiceNumber]]</b>.
{else}
Status har uppdaterats för en eller flera leveranser av din <b class="orderID">#[[order.invoiceNumber]]</b>.
{/if}

Om du har frågor rörande din order kan du kontakta oss via följande länk:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
Ny status: {if $shipment.status == 2}avvaktar leverans {elseif $shipment.status == 3}levererad {elseif $shipment.status == 4}returnerad {else}under behandling{/if}

[[ partial("email/blockItemHeader.tpl") ]]
[[ partial("email/blockShipment.tpl") ]]
------------------------------------------------------------

{/foreach}

[[ partial("email/en/signature.tpl") ]]