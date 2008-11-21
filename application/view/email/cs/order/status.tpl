{'STORE_NAME'|config} Změna stavu objednávky
Vážený(á) {$user.fullName},

{if $order.shipments|@count == 1}
Stav Vaší objednávky č.: {$order.ID} byl změněn.
{else}
Stav jedné nebo víve zásilek z Vaší objednávky č.: {$order.ID} byl změněn.
{/if}

Pokud k této objednávce máte nějaké dotazy, můžete nám poslat email nebo použít následující odkaz:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$order.shipments item=shipment}
Nový stav: {if $shipment.status == 2}čeká na odeslání{elseif $shipment.status == 3}odeslána{elseif $shipment.status == 4}vrácena{else}vyřizuje se{/if}

{include file="email/blockItemHeader.tpl"}
{include file="email/blockShipment.tpl"}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}