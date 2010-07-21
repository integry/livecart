{if !$html}
{include file="email/blockItemHeader.tpl"}
{foreach from=$order.shipments item=shipment}
{include file="email/blockShipment.tpl"}
{/foreach}
------------------------------------------------------------{if 'SHOW_SKU_EMAIL'|config}----------{/if}
{/if}{*html*}
{if $html}
{if !$noTable}<table>{/if}
{include file="email/blockItemHeader.tpl" noTable=true}
{foreach from=$order.shipments item=shipment}
{include file="email/blockShipment.tpl" noTable=true}
{/foreach}
{if !$noTable}</table>{/if}
{/if}{*html*}