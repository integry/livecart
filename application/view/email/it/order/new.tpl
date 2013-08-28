[[ config('STORE_NAME') ]] Conferma Ordine
Gentile {$user.fullName},

Ti ringraziamo per il tuo ordine, che hai effettuato presso [[ config('STORE_NAME') ]].

Se volessi contattarci in merito a questo ordine, ti preghiamo di fare riferimento
a questo numero identificativo: Ordine numero {$order.invoiceNumber}.

Potrai tracciare lo stato del tuo ordine direttamente da questa pagina:
{link controller=user action=viewOrder id=$order.ID url=true}

Nel caso avessi domande in merito a questo ordine, potrai inviarci una comunicazione
direttamente dalla pagina su indicata.

Ti ricordiamo che hai ordinato i seguenti articoli:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/it/signature.tpl"}