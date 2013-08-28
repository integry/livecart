[[ config('STORE_NAME') ]] Orderbekräftelse
Kära [[user.fullName]],

Tack för din order till [[ config('STORE_NAME') ]]. Om du behöver kontakta oss angående din order var god uppge ditt order ID <b class="orderID">#[[order.invoiceNumber]]</b>.

Du kan följa status på din order via den här länken:
{link controller=user action=viewOrder id=$order.ID url=true}

Om du har några frågor rörande din order kan du också använda länken ovan.

Du har beställt följande varor:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]