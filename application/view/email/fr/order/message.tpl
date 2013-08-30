Nouveau message a propos de votre commande sur [[ config('STORE_NAME') ]]
Cher [[user.fullName]],

Un nouveau message a été ajouté a propos de votre commande.

--------------------------------------------------
[[message.text]]
--------------------------------------------------

Vous pouvez répondre a ce message a partir de la page suivante:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

[[ partial("email/fr/signature.tpl") ]]