{'STORE_NAME'|config} Orderbekräftelse
Kära {$user.fullName},

Tack för din order till {'STORE_NAME'|config}. Om du behöver kontakta oss angående din order var god uppge ditt order ID <b class="orderID">#{$order.ID}</b>.

Du kan följa status på din order via den här länken:
{link controller=user action=viewOrder id=$order.ID url=true}

Om du har några frågor rörande din order kan du också använda länken ovan.

Du har beställt följande varor:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}