Ny beskjed ang. bestilling hos {'STORE_NAME'|config}
En kunde har sendt en ny beskjed ang. bestillingsnr. <b class="orderID">#{$order.ID}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Du kan svare på denne fra order management panel:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/no/signature.tpl"}