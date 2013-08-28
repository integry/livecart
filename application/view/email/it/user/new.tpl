Benvenuto su [[ config('STORE_NAME') ]]!
Gentile [[user.fullName]],

Queste sono i dati del tuo account presso il nostro negozio online [[ config('STORE_NAME') ]]:

E-mail: <b>[[user.email]]</b>
Password: <b>[[user.newPassword]]</b>

Dal tuo pannello personalizzato potrai controllare lo stato dei tuoi ordini, controllare lo storico degli
ordini, scaricare files e modificare le informazioni relative agli indirizzi di fatturazione e consegna della merce.

Puoi utilizzare questo indirizzo per accedere al tuo account:
{link controller=user action=login url=true}

{include file="email/it/signature.tpl"}