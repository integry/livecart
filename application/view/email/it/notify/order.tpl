Nuovo Ordine ricevuto da {'STORE_NAME'|config}
Numero Ordine: {$order.ID}

Pannello di Amministrazione Ordine:
{backendOrderUrl order=$order url=true}

Sono stati ordinati i seguenti articoli:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/it/signature.tpl"}