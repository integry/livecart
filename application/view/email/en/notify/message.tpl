New Order Message at {'STORE_NAME'|config}
A customer has added a new message regarding order <b class="orderID">#{$order.invoiceNumber}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

You can add a response from order management panel:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}