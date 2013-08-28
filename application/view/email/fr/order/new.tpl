[[ config('STORE_NAME') ]] Confirmation de la commande
Cher [[user.fullName]],

Merci pour votre commande, Que vous venez tout juste de place sur [[ config('STORE_NAME') ]]. Si vous avez besoin de nous contacter a propos de cette commande, s.v.p indiquer l'identifiant (ID) de la commande <b class="orderID">#[[order.invoiceNumber]]</b>.

Vous pourrez suivre l'évolution de votre commande sur cette page:
{link controller=user action=viewOrder id=$order.ID url=true}

Si vous avez des questions concernant cette commande, vous pouvez nous envoyer un message de la page ci-dessus.

Nous vous rappelons que l'item a été commandé:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/fr/signature.tpl") ]]