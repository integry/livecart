Νέα Παραγγελλία Τοποθετήθηκε στο [[ config('STORE_NAME') ]]
Κωδικός(ID) Παραγγeλλίας: [[order.invoiceNumber]]

Διαχείριση Παραγγελλίας:
{backendOrderUrl order=order url=true}

Τα ακόλουθα είδη έχουν παραγγελθεί:
[[ partial("email/blockOrder.tpl") ]]

[[ partial("email/blockOrderAddresses.tpl") ]]

[[ partial("email/en/signature.tpl") ]]