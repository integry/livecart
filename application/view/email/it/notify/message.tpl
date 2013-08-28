Nuovo Messaggio da un utente [[ config('STORE_NAME') ]]
Un utente ha aggiunto un nuovo messaggio in merito all'ordine <b class="orderID">#[[order.invoiceNumber]]</b>

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Puoi aggiungere una risposta direttamente dal pannello di amministrazione:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

[[ partial("email/it/signature.tpl") ]]