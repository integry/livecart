[[ config('STORE_NAME') ]] Conferma Ordine
Gentile [[user.fullName]],

Ti ringraziamo per il tuo ordine, che hai effettuato presso [[ config('STORE_NAME') ]].

Se volessi contattarci in merito a questo ordine, ti preghiamo di fare riferimento
a questo numero identificativo: Ordine numero [[order.invoiceNumber]].

Potrai tracciare lo stato del tuo ordine direttamente da questa pagina:
[[ fullurl("user/viewOrder" ~ order.ID) ]]

Nel caso avessi domande in merito a questo ordine, potrai inviarci una comunicazione
direttamente dalla pagina su indicata.

Ti ricordiamo che hai ordinato i seguenti articoli:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/it/signature.tpl") ]]