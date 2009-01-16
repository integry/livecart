Benvenuto su {'STORE_NAME'|config}!
Gentile {$user.fullName},

Queste sono i dati del tuo account presso il nostro negozio online {'STORE_NAME'|config}:

E-mail: <b>{$user.email}</b>
Password: <b>{$user.newPassword}</b>

Dal tuo pannello personalizzato potrai controllare lo stato dei tuoi ordini, controllare lo storico degli
ordini, scaricare files e modificare le informazioni relative agli indirizzi di fatturazione e consegna della merce.

Puoi utilizzare questo indirizzo per accedere al tuo account:
{link controller=user action=login url=true}

{include file="email/it/signature.tpl"}