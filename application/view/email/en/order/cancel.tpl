[[ config('STORE_NAME') ]] Order Cancelled
Dear [[user.fullName]],

Your order <b class="orderID">#[[order.invoiceNumber]]</b>, placed at [[ config('STORE_NAME') ]], has been cancelled.

If you have any questions regarding this order, you can send us an email message or contact from the following page:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Items in the cancelled order:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]