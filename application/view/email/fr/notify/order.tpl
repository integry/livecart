Nouvelle commande placée sur {'STORE_NAME'|config}
Commande ID: {$order.invoiceNumber}

Administration de la commande:
{backendOrderUrl order=$order url=true}

The following items have been ordered:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/fr/signature.tpl"}