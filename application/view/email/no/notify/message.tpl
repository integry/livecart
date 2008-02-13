Ny beskjed ang. bestilling hos {'STORE_NAME'|config}
En kunde har sendt en ny beskjed ang. bestillingsnr. #{$order.ID}

--------------------------------------------------
{$message.text}
--------------------------------------------------

Du kan svare på denne fra order management panel:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}