{include file="email/blockItemHeader.tpl"}
{foreach from=$order.shipments item=shipment}
{include file="email/blockShipment.tpl"}
{/foreach}
------------------------------------------------------------