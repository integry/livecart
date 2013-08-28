Nuovo Messaggio per il tuo ordine effettuato su  [[ config('STORE_NAME') ]]
Gentile {$user.fullName},

E' stato aggiunto un nuovo messagio riguardante il tuo ordine.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Puoi rispondere a questo messaggio direttamente da questa pagina:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/it/signature.tpl"}