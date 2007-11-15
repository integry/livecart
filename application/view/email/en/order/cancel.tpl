{'STORE_NAME'|config} Order Cancelled
Dear {$user.fullName},

Your order #{$order.ID}, placed at {'STORE_NAME'|config}, has been cancelled.

If you have any questions regarding this order, you can send us an email message or contact from the following page:
{link controller=user action=viewOrder id=$order.ID url=true}

Items in the cancelled order:
------------------------------------------------------------
Item						   Price	 Qty	  Subtotal
------------------------------------------------------------
{foreach from=$order.shipments item=shipment}
{foreach from=$shipment.items item=item}
{$item.Product.name_lang|truncate:29:"...":"true"|@str_pad:31}{$item.formattedPrice|truncate:9:"..."|@str_pad:10}{$item.count|truncate:8:"..."|@str_pad:9}{$item.formattedSubTotal}
{/foreach}
{/foreach}
------------------------------------------------------------

{include file="email/en/signature.tpl"}