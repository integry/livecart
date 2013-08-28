New Message Regarding Your Order at [[ config('STORE_NAME') ]]
Kære [[user.fullName]],

En ny besked vedrørende din ordre, er blevet tilføjet.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan besvare denne besked fra den følgende side:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}