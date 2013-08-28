Új üzenet rendelés mellé[[ config('STORE_NAME') ]]
Egy vásárló üzenete írt az egyik rendelésseé kapcsolatosan <b class="orderID">#{$order.invoiceNumber}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Válaszolhat erre az üzenetre az adminisztrációs felületből:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}