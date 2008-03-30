Nuovo Messaggio da un utente {'STORE_NAME'|config}
Un utente ha aggiunto un nuovo messaggio in merito all'ordine #{$order.ID}

--------------------------------------------------
{$message.text}
--------------------------------------------------

Puoi aggiungere una risposta direttamente dal pannello di amministrazione:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}