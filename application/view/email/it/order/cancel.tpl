[[ config('STORE_NAME') ]] Ordine Cancellato
Gentile [[user.fullName]],

L'ordine numero <b class="orderID">#[[order.invoiceNumber]]</b>, inserito presso [[ config('STORE_NAME') ]], è stato cancellato.

Nel caso desiderassi effettuare domande in merito a questo ordine, puoi inviarci una email
oppure contattarci direttamente da questa pagina:
{link controller=user action=viewOrder id=$order.ID url=true}

Articoli contenuti nell'ordine cancellato:
[[ partial("email/blockOrderItems.tpl") ]]

[[ partial("email/it/signature.tpl") ]]