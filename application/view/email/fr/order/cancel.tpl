[[ config('STORE_NAME') ]] Commande cancellée
Cher [[user.fullName]],

Votre commande <b class="orderID">#[[order.invoiceNumber]]</b>, placée sur [[ config('STORE_NAME') ]], a été cancellée.

Si vous avez des questions concernant cette commande, vous pouvez nous envoyer un courriel ou nous contacter a partir de la page suivante:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Items de la comande cancellée:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/fr/signature.tpl") ]]