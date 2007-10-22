{'STORE_NAME'|config} jauns klienta paziņojums sakarā ar pasūtījumu
Klients ir pievienojis jaunu paziņojumu pie pasūtījuma #{$order.ID}

--------------------------------------------------
{$message.text}
--------------------------------------------------

Jūs varat pievienot atbildi no administrācijas zonas:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}