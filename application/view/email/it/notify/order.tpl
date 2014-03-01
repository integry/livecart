Nuovo Ordine ricevuto da [[ config('STORE_NAME') ]]
Numero Ordine: [[order.invoiceNumber]]

Pannello di Amministrazione Ordine:
{backendOrderUrl order=order url=true}

Sono stati ordinati i seguenti articoli:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/it/signature.tpl") ]]