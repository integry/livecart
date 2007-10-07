{'STORE_NAME'|config} Order Status Update
Dear {$user.fullName},

{if $order.shipments|@count == 1}
Status has been updated for your order #{$order.ID}.
{else}
Status has been updated for one or more shipments from your order #{$order.ID}.
{/if}

If you have any questions regarding this order, you can send us an email message or contact from the following page:
{link controller=user action=viewOrder id=$order.ID url=true}

{foreach from=$shipments item=shipment}
New status: {if $shipment.status == 2}awaiting shipment{elseif $shipment.status == 3}shipped{elseif $shipment.status == 4}returned{else}processing{/if}

------------------------------------------------------------
Item                           Price     Qty      Subtotal
------------------------------------------------------------
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
------------------------------------------------------------

{/foreach}

{include file="email/en/signature.tpl"}