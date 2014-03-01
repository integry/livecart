Nouvelle commande placée sur [[ config('STORE_NAME') ]]
Commande ID: [[order.invoiceNumber]]

Administration de la commande:
{backendOrderUrl order=order url=true}

The following items have been ordered:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/fr/signature.tpl") ]]