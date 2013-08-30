[[ config('STORE_NAME') ]] Rendelés visszavonva
Tisztelt [[user.fullName]],

Az ön rendelés <b class="orderID">#[[order.invoiceNumber]]</b>, amelyet [[ config('STORE_NAME') ]] adott le, vissza lett vonva.

Ha bármilyen kérdése van a rendeléssel kapcsolatban, üzenetet írhat nekünk a következő címen:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

A visszavont rendelés a következő termékeket tartalmazta:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/en/signature.tpl") ]]