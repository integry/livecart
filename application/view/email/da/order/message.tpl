New Message Regarding Your Order at [[ config('STORE_NAME') ]]
Kære [[user.fullName]],

En ny besked vedrørende din ordre, er blevet tilføjet.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Du kan besvare denne besked fra den følgende side:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/en/signature.tpl") ]]