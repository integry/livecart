Νέα Παραγγελλία Τοποθετήθηκε στο {'STORE_NAME'|config}
Κωδικός(ID) Παραγγeλλίας: {$order.ID}

Διαχείριση Παραγγελλίας:
{backendOrderUrl order=$order url=true}

Τα ακόλουθα είδη έχουν παραγγελθεί:
{include file="email/blockOrder.tpl"}

{include file="email/blockOrderAddresses.tpl"}

{include file="email/en/signature.tpl"}