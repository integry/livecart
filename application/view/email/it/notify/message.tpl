Nuovo Messaggio da un utente {'STORE_NAME'|config}
Un utente ha aggiunto un nuovo messaggio in merito all'ordine <b class="orderID">#{$order.ID}</b>

--------------------------------------------------
{$message.text}
--------------------------------------------------

Puoi aggiungere una risposta direttamente dal pannello di amministrazione:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/it/signature.tpl"}