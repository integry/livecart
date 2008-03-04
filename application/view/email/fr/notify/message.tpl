Nouveau Message pour la commande sur {'STORE_NAME'|config}
Un client a laissé un nouveau message a propos de la commande #{$order.ID}

--------------------------------------------------
{$message.text}
--------------------------------------------------

Vous pouvez répondre a partir du tableau de gestion des commandes:
{backendOrderUrl order=$order url=true}#tabOrderCommunication__

{include file="email/en/signature.tpl"}