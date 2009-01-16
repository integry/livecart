{'STORE_NAME'|config} Confirmation de la commande
Cher {$user.fullName},

Merci pour votre commande, Que vous venez tout juste de place sur {'STORE_NAME'|config}. Si vous avez besoin de nous contacter a propos de cette commande, s.v.p indiquer l'identifiant (ID) de la commande <b class="orderID">#{$order.ID}</b>.

Vous pourrez suivre l'évolution de votre commande sur cette page:
{link controller=user action=viewOrder id=$order.ID url=true}

Si vous avez des questions concernant cette commande, vous pouvez nous envoyer un message de la page ci-dessus.

Nous vous rappelons que l'item a été commandé:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/fr/signature.tpl"}