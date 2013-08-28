[[ config('STORE_NAME') ]] Rendelés visszavonva
Tisztelt [[user.fullName]],

Az ön rendelés <b class="orderID">#[[order.invoiceNumber]]</b>, amelyet [[ config('STORE_NAME') ]] adott le, vissza lett vonva.

Ha bármilyen kérdése van a rendeléssel kapcsolatban, üzenetet írhat nekünk a következő címen:
{link controller=user action=viewOrder id=$order.ID url=true}

A visszavont rendelés a következő termékeket tartalmazta:
{include file="email/blockOrderItems.tpl"}

{include file="email/en/signature.tpl"}