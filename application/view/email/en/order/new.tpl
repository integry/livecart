{'STORE_NAME'|config} Order Confirmation
Dear {$user.fullName},

Thank you for your order, which you just placed at {'STORE_NAME'|config}. If you need to contact us regarding this order, please quote the order ID <b class="orderID">#{$order.ID}</b>.

You will be able to track the progress of your order at this page:
{link controller=user action=viewOrder id=$order.ID url=true}

If you have any questions regarding this order, you can send us a message from the above page as well.

We remind that the following items have been ordered:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}