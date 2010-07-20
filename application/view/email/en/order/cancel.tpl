{'STORE_NAME'|config} Order Cancelled
Dear {$user.fullName},

Your order <b class="orderID">#{$order.invoiceNumber}</b>, placed at {'STORE_NAME'|config}, has been cancelled.

If you have any questions regarding this order, you can send us an email message or contact from the following page:
{link controller=user action=viewOrder id=$order.ID url=true}

Items in the cancelled order:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}