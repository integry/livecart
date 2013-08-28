New Order Message at [[ config('STORE_NAME') ]]
En kunder har tilføet en ny ordre: <b class="orderID">#[[order.invoiceNumber]]</b>

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan behandle orden i fanepladet under 'Ordrer':
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}