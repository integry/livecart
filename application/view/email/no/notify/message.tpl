Ny beskjed ang. bestilling hos [[ config('STORE_NAME') ]]
En kunde har sendt en ny beskjed ang. bestillingsnr. <b class="orderID">#[[order.invoiceNumber]]</b>

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan svare på denne fra order management panel:
{backendOrderUrl order=order url=true}#tabOrderCommunication__

[[ partial("email/no/signature.tpl") ]]