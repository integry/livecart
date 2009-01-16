{'STORE_NAME'|config} Commande cancellée
Cher {$user.fullName},

Votre commande <b class="orderID">#{$order.ID}</b>, placée sur {'STORE_NAME'|config}, a été cancellée.

Si vous avez des questions concernant cette commande, vous pouvez nous envoyer un courriel ou nous contacter a partir de la page suivante:
{link controller=user action=viewOrder id=$order.ID url=true}

Items de la comande cancellée:
{include file="email/blockOrderItems.tpl"}

{include file="email/fr/signature.tpl"}