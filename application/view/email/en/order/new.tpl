[[ config('STORE_NAME') ]] Order Confirmation
Dear [[user.fullName]],

Thank you for your order, which you just placed at [[ config('STORE_NAME') ]]. If you need to contact us regarding this order, please quote the order ID <b class="orderID">#[[order.invoiceNumber]]</b>.

You will be able to track the progress of your order at this page:
{link controller=user action=viewOrder id=$order.ID url=true}

If you have any questions regarding this order, you can send us a message from the above page as well.

We remind you that the following items have been ordered:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]