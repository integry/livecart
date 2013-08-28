Nuovo Ordine ricevuto da [[ config('STORE_NAME') ]]
Numero Ordine: {$order.invoiceNumber}

Pannello di Amministrazione Ordine:
{backendOrderUrl order=$order url=true}

Sono stati ordinati i seguenti articoli:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/it/signature.tpl"}