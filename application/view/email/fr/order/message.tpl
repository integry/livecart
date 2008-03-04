Nouveau message a propos de votre commande sur {'STORE_NAME'|config}
Cher {$user.fullName},

Un nouveau message a été ajouté a propos de votre commande.

--------------------------------------------------
{$message.text}
--------------------------------------------------

Vous pouvez répondre a ce message a partir de la page suivante:
{link controller=user action=viewOrder id=$order.ID url=true}

{include file="email/en/signature.tpl"}