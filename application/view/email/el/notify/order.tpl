Νέα Παραγγελλία Τοποθετήθηκε στο [[ config('STORE_NAME') ]]
Κωδικός(ID) Παραγγeλλίας: {$order.invoiceNumber}

Διαχείριση Παραγγελλίας:
{backendOrderUrl order=$order url=true}

Τα ακόλουθα είδη έχουν παραγγελθεί:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}